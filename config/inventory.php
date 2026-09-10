<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inventory & Order Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized configuration parameters for the inventory and ordering system.
    | Modifying these values updates system-wide behavior without hardcoded logic.
    |
    */

    'low_stock_threshold' => env('INVENTORY_LOW_STOCK_THRESHOLD', 5),

    'tax_rounding_mode' => env('INVENTORY_TAX_ROUNDING_MODE', PHP_ROUND_HALF_UP),

    'currency_symbol' => env('INVENTORY_CURRENCY_SYMBOL', '$'),

    'currency_code' => env('INVENTORY_CURRENCY_CODE', 'USD'),

    'queue' => env('INVENTORY_QUEUE_NAME', 'default'),
];
