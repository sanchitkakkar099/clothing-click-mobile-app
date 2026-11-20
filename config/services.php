<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_CALLBACK_URL'),
    ],

	'mailjet' => [
		'key' => env('MAILJET_APIKEY'),
		'secret' => env('MAILJET_SECRET'),
		'from_address' => env('MAIL_FROM_ADDRESS'),
		'from_name' => env('MAIL_FROM_NAME'),
	],


	'stripe' => [
		'secret' => env('STRIPE_SECRET_KEY'),
		'publishable' => env('STRIPE_PUBLISH_KEY'),
	],

	'shopify' => [
		'access_token' => env('SHOPIFY_ACCESS_TOKEN'),
		// JSON map of vendor store domain => access token, e.g. {"drip-click-2.myshopify.com":"shpat_..."}
		'vendor_tokens' => env('SHOPIFY_VENDOR_TOKENS'),
		// vendor platform that holds each vendor store's Shopify token (server-to-server, shared key)
		'vendor_platform_url' => env('VENDOR_PLATFORM_URL', 'https://vendor.clothingclick.ca/api/storeappInfo'),
		'vendor_platform_key' => env('VENDOR_PLATFORM_KEY'),
		'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),
	],

];
