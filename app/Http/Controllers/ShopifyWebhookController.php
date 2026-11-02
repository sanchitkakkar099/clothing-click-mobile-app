<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\posts;

/**
 * Receives Shopify product webhooks (products/create, products/update,
 * products/delete) and keeps the app's posts in sync in real time.
 */
class ShopifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.shopify.webhook_secret');
        $body   = $request->getContent();

        // Verify the request really comes from Shopify (HMAC-SHA256 of the raw body)
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        if (empty($secret) || empty($hmac) || !hash_equals(base64_encode(hash_hmac('sha256', $body, $secret, true)), $hmac)) {
            \Log::warning('Shopify webhook rejected: invalid signature');
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $topic   = $request->header('X-Shopify-Topic');
        $product = json_decode($body);
        if (!$product || empty($product->id)) {
            return response()->json(['status' => false, 'message' => 'Invalid payload'], 400);
        }

        // Shopify expects a 2xx within a few seconds. Syncing downloads images, so
        // answer first and do the work after the response has been sent.
        $content = json_encode(['status' => true]);
        $response = response($content, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Length', strlen($content))
            ->header('Connection', 'close');

        app()->terminating(function () use ($topic, $product) {
            ignore_user_abort(true);
            set_time_limit(0);
            try {
                if ($topic === 'products/delete') {
                    // Product removed on Shopify: hide the post instead of deleting it
                    posts::where('shopify_product_id', $product->id)->update(['is_active' => 0]);
                } else {
                    app(HomeController::class)->syncShopifyProduct($product);
                }
            } catch (\Throwable $e) {
                \Log::error('Shopify webhook sync error', [
                    'topic'      => $topic,
                    'product_id' => $product->id,
                    'message'    => $e->getMessage(),
                    'line'       => $e->getLine(),
                ]);
            }
        });

        return $response;
    }
}
