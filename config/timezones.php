<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Timezones for Camps
    |--------------------------------------------------------------------------
    |
    | Common timezones for sports camps. Directors select from this list
    | when creating a camp based on the camp's physical location.
    |
    */

    'available' => [
        // North America
        'America/New_York' => 'Eastern Time (ET)',
        'America/Chicago' => 'Central Time (CT)',
        'America/Denver' => 'Mountain Time (MT)',
        'America/Los_Angeles' => 'Pacific Time (PT)',
        'America/Phoenix' => 'Arizona Time (MST)',
        'America/Anchorage' => 'Alaska Time (AKT)',
        'America/Adak' => 'Hawaii-Aleutian Time (HST)',

        // Europe
        'Europe/London' => 'London (GMT/BST)',
        'Europe/Paris' => 'Paris (CET/CEST)',
        'Europe/Berlin' => 'Berlin (CET/CEST)',
        'Europe/Rome' => 'Rome (CET/CEST)',
        'Europe/Madrid' => 'Madrid (CET/CEST)',
        'Europe/Athens' => 'Athens (EET/EEST)',
        'Europe/Moscow' => 'Moscow (MSK)',

        // Asia
        'Asia/Dubai' => 'Dubai (GST)',
        'Asia/Kolkata' => 'India (IST)',
        'Asia/Dhaka' => 'Bangladesh (BST)',
        'Asia/Bangkok' => 'Bangkok (ICT)',
        'Asia/Singapore' => 'Singapore (SGT)',
        'Asia/Hong_Kong' => 'Hong Kong (HKT)',
        'Asia/Tokyo' => 'Tokyo (JST)',
        'Asia/Shanghai' => 'Shanghai (CST)',

        // Australia & Pacific
        'Australia/Sydney' => 'Sydney (AEDT/AEST)',
        'Australia/Melbourne' => 'Melbourne (AEDT/AEST)',
        'Australia/Brisbane' => 'Brisbane (AEST)',
        'Australia/Perth' => 'Perth (AWST)',
        'Pacific/Auckland' => 'Auckland (NZDT/NZST)',

        // Middle East
        'Asia/Jerusalem' => 'Jerusalem (IST)',
        'Asia/Riyadh' => 'Riyadh (AST)',
        'Asia/Kuwait' => 'Kuwait (AST)',

        // Africa
        'Africa/Cairo' => 'Cairo (EET)',
        'Africa/Johannesburg' => 'Johannesburg (SAST)',
        'Africa/Lagos' => 'Lagos (WAT)',
        'Africa/Nairobi' => 'Nairobi (EAT)',

        // South America
        'America/Sao_Paulo' => 'São Paulo (BRT)',
        'America/Argentina/Buenos_Aires' => 'Buenos Aires (ART)',
        'America/Santiago' => 'Santiago (CLT)',
        'America/Lima' => 'Lima (PET)',
        'America/Bogota' => 'Bogotá (COT)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Timezone Detection
    |--------------------------------------------------------------------------
    |
    | Auto-detect timezone based on coordinates when not explicitly set
    |
    */

    'coordinate_mapping' => [
        // North America
        ['lat_min' => 24, 'lat_max' => 50, 'lng_min' => -125, 'lng_max' => -66, 'timezone' => 'America/New_York'],

        // Europe
        ['lat_min' => 35, 'lat_max' => 71, 'lng_min' => -10, 'lng_max' => 40, 'timezone' => 'Europe/London'],

        // Asia - India
        ['lat_min' => 8, 'lat_max' => 35, 'lng_min' => 68, 'lng_max' => 97, 'timezone' => 'Asia/Kolkata'],

        // Asia - Bangladesh
        ['lat_min' => 20, 'lat_max' => 27, 'lng_min' => 88, 'lng_max' => 93, 'timezone' => 'Asia/Dhaka'],

        // Asia - Southeast
        ['lat_min' => -10, 'lat_max' => 28, 'lng_min' => 95, 'lng_max' => 141, 'timezone' => 'Asia/Singapore'],

        // Australia
        ['lat_min' => -44, 'lat_max' => -10, 'lng_min' => 113, 'lng_max' => 154, 'timezone' => 'Australia/Sydney'],

        // Middle East
        ['lat_min' => 12, 'lat_max' => 42, 'lng_min' => 34, 'lng_max' => 63, 'timezone' => 'Asia/Dubai'],
    ],
];
