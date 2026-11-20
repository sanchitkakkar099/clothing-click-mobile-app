<?php

namespace App\Services;

use App\ProductVariant;
use App\size;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the order of a purchased item in the Shopify store of the vendor who owns it.
 *
 * The vendor is the app user (role 8) that owns the post: Shopify products are linked to
 * users by the product "vendor" name (see HomeController::syncShopifyProduct), and the
 * user's `website` holds the vendor's *.myshopify.com domain. A vendor is "connected" when
 * a token for that domain exists in SHOPIFY_VENDOR_TOKENS. Products of vendors that are not
 * connected stay parent-store only. Failures never break the customer's order.
 */
class VendorShopifyOrderService
{
    const API_VERSION = '2023-10';

    /**
     * @param \App\posts $post            purchased post
     * @param string|int $parentVariantId Shopify variant id in the parent store
     * @param array      $address         billing/shipping address as sent to the parent store
     * @param array      $customer        ['email' => ...]
     * @param float      $amount          paid amount for this item
     * @param array      $meta            ['app_order_id' => int, 'parent_order' => string]
     * @return array ['status' => string, 'shop' => ?string, 'shopify_order_id' => ?string, 'error' => ?string]
     */
    public function createForPost($post, $parentVariantId, array $address, array $customer, $amount, array $meta = [])
    {
        $result = ['status' => 'skipped_parent_only', 'shop' => null, 'shopify_order_id' => null, 'error' => null];
        $vendor = null;

        try {
            $vendor = $post ? User::where([['id', $post->user_id], ['role_id', 8]])->first() : null;
            $shop   = $vendor ? $this->shopDomain($vendor->website) : null;
            $result['shop'] = $shop;

            // Not a vendor product -> only the parent store gets the order
            if (!$vendor || !$shop) {
                return $this->record($post, $vendor, $meta, $result);
            }

            $token = $this->tokenFor($shop);
            if (!$token) {
                $result['status'] = 'skipped_no_token';
                return $this->record($post, $vendor, $meta, $result);
            }

            $variantId = $this->findVendorVariantId($shop, $token, $post, $parentVariantId);
            if (!$variantId) {
                $result['status'] = 'failed_no_variant_match';
                $result['error']  = 'Product/variant not found in vendor store';
                return $this->record($post, $vendor, $meta, $result);
            }

            $payload = [
                'order' => [
                    'line_items'          => [['variant_id' => $variantId, 'quantity' => 1]],
                    'customer'            => $customer,
                    'billing_address'     => $address,
                    'shipping_address'    => $address,
                    'financial_status'    => 'paid',
                    'transactions'        => [['kind' => 'sale', 'status' => 'success', 'amount' => $amount]],
                    'inventory_behaviour' => 'decrement_obeying_policy',
                    'note'                => 'Clothing Click app order #' . ($meta['app_order_id'] ?? '') . ' (parent order ' . ($meta['parent_order'] ?? '') . ')',
                    'tags'                => 'clothing-click-app',
                ],
            ];

            $resp = Http::withHeaders(['X-Shopify-Access-Token' => $token])
                ->timeout(20)
                ->post("https://{$shop}/admin/api/" . self::API_VERSION . "/orders.json", $payload);

            $orderId = $resp->json('order.id');
            if ($resp->successful() && $orderId) {
                $result['status'] = 'created';
                $result['shopify_order_id'] = (string) $orderId;
            } else {
                $result['status'] = 'failed_api';
                $result['error']  = 'HTTP ' . $resp->status() . ' ' . substr((string) $resp->body(), 0, 300);
            }
        } catch (\Throwable $e) {
            $result['status'] = 'failed_exception';
            $result['error']  = substr($e->getMessage(), 0, 300);
        }

        return $this->record($post, $vendor, $meta, $result);
    }

    /** "https://Drip-Click-2.myshopify.com/" -> "drip-click-2.myshopify.com" (null if not a myshopify domain) */
    public function shopDomain($website)
    {
        $d = strtolower(trim((string) $website));
        $d = preg_replace('#^https?://#', '', $d);
        $d = trim(explode('/', $d)[0]);

        return preg_match('/^[a-z0-9][a-z0-9\-]*\.myshopify\.com$/', $d) ? $d : null;
    }

    /**
     * Access token of a vendor store. Lookup order:
     *  1. SHOPIFY_VENDOR_TOKENS (JSON map domain => token), if set
     *  2. the vendor platform (VENDOR_PLATFORM_KEY), the single source of truth for vendor tokens
     * Returns null when the vendor is not connected / the platform has no token for it.
     */
    public function tokenFor($shop)
    {
        $map = config('services.shopify.vendor_tokens');
        if (is_string($map)) {
            $map = json_decode($map, true);
        }
        if (is_array($map) && !empty($map[$shop])) {
            return $map[$shop];
        }

        $key = config('services.shopify.vendor_platform_key');
        if (!$key) {
            return null;
        }

        try {
            $resp = Http::withHeaders(['X-Api-Key' => $key])->timeout(10)
                ->get(rtrim(config('services.shopify.vendor_platform_url'), '/') . '/store-credentials', ['store_domain' => $shop]);

            return $resp->successful() ? ($resp->json('data.access_token') ?: null) : null;
        } catch (\Throwable $e) {
            Log::error('Vendor platform token lookup failed: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Match the purchased item to a variant of the same product in the vendor store:
     * product by title, then variant by SKU, then by size/option value, else the only variant.
     */
    protected function findVendorVariantId($shop, $token, $post, $parentVariantId)
    {
        $local = ProductVariant::where('shopify_variant_id', $parentVariantId)->first();
        $sizeName = ($local && $local->size) ? size::where('id', $local->size)->value('name') : null;

        $resp = Http::withHeaders(['X-Shopify-Access-Token' => $token])->timeout(20)
            ->get("https://{$shop}/admin/api/" . self::API_VERSION . "/products.json", [
                'limit' => 250, 'fields' => 'id,title,vendor,variants',
            ]);
        if (!$resp->successful()) {
            return null;
        }

        $norm = fn($s) => strtolower(trim(preg_replace('/\s+/', ' ', (string) $s)));
        $product = collect($resp->json('products') ?? [])->first(fn($p) => $norm($p['title']) === $norm($post->title));
        if (!$product) {
            return null;
        }

        $variants = collect($product['variants'] ?? []);
        if ($local && $local->sku) {
            $v = $variants->first(fn($x) => $norm($x['sku'] ?? '') === $norm($local->sku));
            if ($v) {
                return $v['id'];
            }
        }
        $wanted = array_filter([$norm($local->title ?? ''), $norm($sizeName)]);
        if ($wanted) {
            $v = $variants->first(fn($x) => collect([$x['option1'] ?? '', $x['option2'] ?? '', $x['option3'] ?? '', $x['title'] ?? ''])
                ->contains(fn($o) => in_array($norm($o), $wanted, true)));
            if ($v) {
                return $v['id'];
            }
        }

        return $variants->count() === 1 ? $variants->first()['id'] : null;
    }

    /** Log the outcome (and keep a row per attempt when the vendor_orders table exists). */
    protected function record($post, $vendor, array $meta, array $result)
    {
        if (strpos($result['status'], 'failed') === 0) {
            Log::error('Vendor Shopify order failed', $result + ['app_order_id' => $meta['app_order_id'] ?? null, 'product_id' => $post->id ?? null]);
        }
        try {
            if (Schema::hasTable('vendor_orders')) {
                DB::table('vendor_orders')->insert([
                    'order_id'         => $meta['app_order_id'] ?? null,
                    'product_id'       => $post->id ?? null,
                    'vendor_user_id'   => $vendor->id ?? null,
                    'shop_domain'      => $result['shop'],
                    'shopify_order_id' => $result['shopify_order_id'],
                    'status'           => $result['status'],
                    'error'            => $result['error'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('vendor_orders insert failed: ' . $e->getMessage());
        }

        return $result;
    }
}
