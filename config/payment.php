<?php

return [
    'success_url' => env('PAYMENT_SUCCESS_URL', env('FRONTEND') . '/referee-dashboard/payment-success'),
    'cancel_url' => env('PAYMENT_CANCEL_URL', env('FRONTEND') . '/referee-dashboard/payment-cancel'),
    'retry_cooldown' => env('PAYMENT_RETRY_COOLDOWN', 1), // minutes
    'session_expiry' => env('PAYMENT_SESSION_EXPIRY', 1), // minutes
];
