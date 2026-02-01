<?php

namespace App\Helpers;

use Carbon\Carbon;

class HandlesTimezones
{
    /**
     * Auto-detect timezone from coordinates
     */
    public static function detectFromCoordinates(?float $latitude, ?float $longitude): string
    {
        if (!$latitude || !$longitude) {
            return config('app.timezone', 'UTC');
        }

        $mappings = config('timezones.coordinate_mapping', []);

        foreach ($mappings as $region) {
            if (
                $latitude >= $region['lat_min'] &&
                $latitude <= $region['lat_max'] &&
                $longitude >= $region['lng_min'] &&
                $longitude <= $region['lng_max']
            ) {
                return $region['timezone'];
            }
        }

        return config('app.timezone', 'UTC');
    }

    /**
     * Get timezone display name
     */
    public static function getDisplayName(string $timezone): string
    {
        $available = config('timezones.available', []);
        return $available[$timezone] ?? $timezone;
    }

    /**
     * Convert time to specific timezone for display
     */
    public static function convertToTimezone($dateTime, string $timezone, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$dateTime) {
            return '';
        }

        return Carbon::parse($dateTime)
            ->timezone($timezone)
            ->format($format);
    }

    /**
     * Convert time from specific timezone to UTC
     */
    public static function convertToUTC($dateTime, string $fromTimezone): Carbon
    {
        return Carbon::parse($dateTime, $fromTimezone)->utc();
    }

    /**
     * Get timezone offset difference between two timezones
     */
    public static function getOffsetText(string $campTimezone, ?string $userTimezone = null): ?string
    {
        if (!$userTimezone || $campTimezone === $userTimezone) {
            return null;
        }

        $now = Carbon::now();
        $campOffset = $now->copy()->timezone($campTimezone)->offsetHours;
        $userOffset = $now->copy()->timezone($userTimezone)->offsetHours;
        $diff = $campOffset - $userOffset;

        if ($diff > 0) {
            return "Camp is {$diff} hours ahead of your timezone";
        } elseif ($diff < 0) {
            return "Camp is " . abs($diff) . " hours behind your timezone";
        }

        return null;
    }

    /**
     * Get all available timezones
     */
    public static function getAllTimezones(): array
    {
        return config('timezones.available', []);
    }

    /**
     * Validate timezone string
     */
    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list());
    }
}
