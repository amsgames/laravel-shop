<?php

/**
 * This file is part of Amsgames\LaravelShop,
 * Shop functionality for Laravel.
 *
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Shop name
    |--------------------------------------------------------------------------
    |
    | Shop name.
    |
    */
    'name' => 'Laravel Shop',

    /*
    |--------------------------------------------------------------------------
    | Cart Model
    |--------------------------------------------------------------------------
    |
    | This is the Cart model used by LaravelShop to create correct relations.
    | Update the model if it is in a different namespace.
    |
    */
    'cart' => 'App\Cart',

    /*
    |--------------------------------------------------------------------------
    | Cart Database Table
    |--------------------------------------------------------------------------
    |
    | This is the table used by LaravelShop to save cart data to the database.
    |
    */
    'cart_table' => 'cart',

    /*
    |--------------------------------------------------------------------------
    | Order Model
    |--------------------------------------------------------------------------
    |
    | This is the Order model used by LaravelShop to create correct relations.
    | Update the model if it is in a different namespace.
    |
    */
    'order' => 'App\Order',

    /*
    |--------------------------------------------------------------------------
    | Order Database Table
    |--------------------------------------------------------------------------
    |
    | This is the table used by LaravelShop to save order data to the database.
    |
    */
    'order_table' => 'orders',

    /*
    |--------------------------------------------------------------------------
    | Order Status Database Table
    |--------------------------------------------------------------------------
    |
    | This is the table used by LaravelShop to save order status data to the database.
    |
    */
    'order_status_table' => 'order_status',

    /*
    |--------------------------------------------------------------------------
    | Item Model
    |--------------------------------------------------------------------------
    |
    | This is the Item model used by LaravelShop to create correct relations.
    | Update the model if it is in a different namespace.
    |
    */
    'item' => 'App\Item',

    /*
    |--------------------------------------------------------------------------
    | Item Database Table
    |--------------------------------------------------------------------------
    |
    | This is the table used by LaravelShop to save cart data to the database.
    |
    */
    'item_table' => 'items',

    /*
    |--------------------------------------------------------------------------
    | Transaction Model
    |--------------------------------------------------------------------------
    |
    | This is the Transaction model used by LaravelShop to create correct relations.
    | Update the model if it is in a different namespace.
    |
    */
    'transaction' => 'App\Transaction',

    /*
    |--------------------------------------------------------------------------
    | Transaction Database Table
    |--------------------------------------------------------------------------
    |
    | This is the table used by LaravelShop to save cart data to the database.
    |
    */
    'transaction_table' => 'transactions',

    /*
    |--------------------------------------------------------------------------
    | Coupon Model
    |--------------------------------------------------------------------------
    |
    | This is the Coupon model used by LaravelShop to create correct relations.
    | Update the model if it is in a different namespace.
    |
    */
    'coupon' => 'App\Coupon',

    /*
    |--------------------------------------------------------------------------
    | Coupon Database Table
    |--------------------------------------------------------------------------
    |
    | This is the table used by LaravelShop to save order data to the database.
    |
    */
    'coupon_table' => 'coupons',

    /*
    |--------------------------------------------------------------------------
    | Shop currency code
    |--------------------------------------------------------------------------
    |
    | Currency to use within shop.
    |
    */
    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Shop currency symbol
    |--------------------------------------------------------------------------
    |
    | Currency symbol to use within shop.
    |
    */
    'currency_symbol' => '$',

    /*
    |--------------------------------------------------------------------------
    | Shop tax
    |--------------------------------------------------------------------------
    |
    | Tax percentage to apply to all items. Value must be in decimal.
    |
    | Tax to apply:            8%
    | Tax config value:        0.08
    |
    */
    'tax' => 0.0,

    /*
    |--------------------------------------------------------------------------
    | Format with which to display prices across the store.
    |--------------------------------------------------------------------------
    |
    | :symbol   = Currency symbol. i.e. "$"
    | :price    = Price. i.e. "0.99"
    | :currency = Currency code. i.e. "USD"
    |
    | Example format: ':symbol:price (:currency)'
    | Example result: '$0.99 (USD)'
    |
    */
    'display_price_format' => ':symbol:price',

    /*
    |--------------------------------------------------------------------------
    | Allow multiple coupons
    |--------------------------------------------------------------------------
    |
    | Flag that indicates if user can apply more that one coupon to cart or orders.
    |
    */
    'allow_multiple_coupons' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache shop calculations
    |--------------------------------------------------------------------------
    |
    | Caches shop calculations, such as item count, cart total amount and similar.
    | Cache is forgotten when adding or removing items.
    | If not cached, calculations will be done every time their attributes are called.
    | This configuration option exists if you don't wish to overload your cache.
    |
    */
    'cache_calculations' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache calculations minutes
    |--------------------------------------------------------------------------
    |
    | Amount of minutes to cache calculations.
    |
    */
    'cache_calculations_minutes' => 15,

    /*
    |--------------------------------------------------------------------------
    | Order status lock
    |--------------------------------------------------------------------------
    |
    | Order status where the order will remain locked from modifications.
    |
    */
    'order_status_lock' => [],

    /*
    |--------------------------------------------------------------------------
    | Order status placement
    |--------------------------------------------------------------------------
    |
    | Status to set when the order is placed and created by the cart.
    |
    */
    'order_status_placement' => 'pending',

    /*
    |--------------------------------------------------------------------------
    | Order status placement
    |--------------------------------------------------------------------------
    |
    | Status that determines if an order has been established and if items were purchased.
    |
    */
    'order_status_purchase' => ['completed', 'in_process'],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | List of payment gateways.
    |
    */
    'gateways' => [
        'paypal'            =>  Amsgames\LaravelShopGatewayPaypal\GatewayPayPal::class,
        'paypalExpress'     =>  Amsgames\LaravelShopGatewayPaypal\GatewayPayPalExpress::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gatewall payment callback
    |--------------------------------------------------------------------------
    |
    | Which route to call for gateway callbacks.
    |
    */
    'callback_route' => 'shop.callback',

    /*
    |--------------------------------------------------------------------------
    | Redirect route after callback
    |--------------------------------------------------------------------------
    |
    | Which route to call after the callback has been processed.
    |
    */
    'callback_redirect_route' => '/',

];