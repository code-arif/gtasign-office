<?php

namespace App\Traits;

use Carbon\Carbon;

trait HandlesTimezones
{
    /**
     * Auto-detect timezone from coordinates
     */
    public static function detectTimezoneFromCoordinates(?float $latitude, ?float $longitude): string
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
    public static function getTimezoneDisplayName(string $timezone): string
    {
        $available = config('timezones.available', []);
        return $available[$timezone] ?? $timezone;
    }

    /**
     * Convert time to camp timezone for display
     */
    public function toCampTime($dateTime, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$dateTime) {
            return '';
        }

        $campTimezone = $this->timezone ?? config('app.timezone');

        return Carbon::parse($dateTime)
            ->timezone($campTimezone)
            ->format($format);
    }

    /**
     * Convert time to user's timezone
     */
    public function toUserTime($dateTime, ?string $userTimezone = null, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$dateTime) {
            return '';
        }

        $timezone = $userTimezone ?? request()->header('X-Timezone') ?? config('app.timezone');

        return Carbon::parse($dateTime)
            ->timezone($timezone)
            ->format($format);
    }

    /**
     * Get timezone offset difference between camp and user
     */
    public function getTimezoneOffsetText(?string $userTimezone = null): string
    {
        $campTimezone = $this->timezone ?? config('app.timezone');
        $userTimezone = $userTimezone ?? request()->header('X-Timezone') ?? config('app.timezone');

        if ($campTimezone === $userTimezone) {
            return '';
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

        return '';
    }

    /**
     * Store time in UTC from camp timezone
     */
    public static function fromCampTime($dateTime, string $campTimezone): Carbon
    {
        return Carbon::parse($dateTime, $campTimezone)->utc();
    }
}
