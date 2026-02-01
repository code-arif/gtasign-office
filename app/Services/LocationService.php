<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    protected $apiKey;
    protected $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json';

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }

    /**
     * Get formatted location name from coordinates
     *
     * @param float $latitude
     * @param float $longitude
     * @param string $preferredType - 'short' or 'full' or 'address'
     * @return array
     */
    public function getLocationFromCoordinates($latitude, $longitude, $preferredType = 'short')
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'message' => 'Google Maps API key not configured',
                'location_name' => null,
            ];
        }

        try {
            $response = Http::get($this->baseUrl, [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $this->apiKey,
                'language' => 'en'
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch location data',
                    'location_name' => null,
                ];
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                return [
                    'success' => false,
                    'message' => 'No location found for coordinates',
                    'location_name' => null,
                ];
            }

            $result = $data['results'][0];

            // Extract different types of location names
            $locationData = $this->extractLocationData($result);

            // Choose location name based on preference
            $locationName = $this->selectLocationName($locationData, $preferredType);

            return [
                'success' => true,
                'location_name' => $locationName,
                'formatted_address' => $result['formatted_address'],
                'all_data' => $locationData,
                'place_id' => $result['place_id'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Location fetch error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error fetching location: ' . $e->getMessage(),
                'location_name' => null,
            ];
        }
    }

    /**
     * Extract location components
     */
    protected function extractLocationData($result)
    {
        $components = $result['address_components'];
        $locationData = [
            'premise' => null,
            'street_number' => null,
            'route' => null,
            'neighborhood' => null,
            'sublocality' => null,
            'locality' => null,
            'administrative_area_level_2' => null,
            'administrative_area_level_1' => null,
            'country' => null,
            'postal_code' => null,
        ];

        foreach ($components as $component) {
            $types = $component['types'];

            foreach ($types as $type) {
                if (array_key_exists($type, $locationData)) {
                    $locationData[$type] = $component['long_name'];
                }
            }
        }

        return $locationData;
    }

    /**
     * Select appropriate location name based on preference
     */
    protected function selectLocationName($locationData, $preferredType = 'short')
    {
        switch ($preferredType) {
            case 'short':
                // Priority: locality (city) > administrative_area_level_2 > administrative_area_level_1
                return $locationData['locality']
                    ?? $locationData['administrative_area_level_2']
                    ?? $locationData['administrative_area_level_1']
                    ?? 'Unknown Location';

            case 'detailed':
                // Include neighborhood/sublocality if available
                $parts = array_filter([
                    $locationData['premise'],
                    $locationData['neighborhood'] ?? $locationData['sublocality'],
                    $locationData['locality'],
                    $locationData['administrative_area_level_1'],
                ]);
                return implode(', ', $parts) ?: 'Unknown Location';

            case 'address':
                // Full street address
                $parts = array_filter([
                    $locationData['street_number'],
                    $locationData['route'],
                    $locationData['locality'],
                ]);
                return implode(' ', $parts) ?: 'Unknown Location';

            case 'full':
            default:
                // Complete address with city, state
                $parts = array_filter([
                    $locationData['locality'],
                    $locationData['administrative_area_level_1'],
                    $locationData['country'],
                ]);
                return implode(', ', $parts) ?: 'Unknown Location';
        }
    }

    /**
     * Verify if coordinates are valid
     */
    public function validateCoordinates($latitude, $longitude)
    {
        return $latitude >= -90 && $latitude <= 90 &&
            $longitude >= -180 && $longitude <= 180;
    }
}
