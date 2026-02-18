<?php

return [
    // 'auto_complete_days' => env('ORDER_AUTO_COMPLETE_DAYS', 7),
    'auto_complete_days' => (int) env('ORDER_AUTO_COMPLETE_DAYS', 7),
    'auto_accept_days'   => env('ORDER_AUTO_ACCEPT_DAYS', 3),
    'escrow_hold_days'   => env('ORDER_ESCROW_HOLD_DAYS', 14),
    'platform_fee_pct'   => env('ORDER_PLATFORM_FEE_PCT', 10),
];
