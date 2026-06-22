<?php

namespace App\Services;

class GeolocationService
{
    public function geocodeAddress(string $address): ?array
    {
        if (empty($address)) {
            return null;
        }

        $url = 'https://nominatim.openstreetmap.org/search?'.http_build_query([
            'q' => $address,
            'format' => 'json',
            'limit' => 1,
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AfricanMarket/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || ! $response) {
            return null;
        }

        $data = json_decode($response, true);

        if (empty($data)) {
            return null;
        }

        $result = $data[0];

        return [
            'latitude' => (float) $result['lat'],
            'longitude' => (float) $result['lon'],
            'display_name' => $result['display_name'] ?? null,
        ];
    }

    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/reverse?'.http_build_query([
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'json',
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AfricanMarket/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || ! $response) {
            return null;
        }

        $data = json_decode($response, true);

        if (empty($data)) {
            return null;
        }

        $address = $data['address'] ?? [];

        return [
            'display_name' => $data['display_name'] ?? null,
            'address' => $address['road'] ?? $address['suburb'] ?? null,
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
            'state' => $address['state'] ?? null,
            'country' => $address['country'] ?? null,
            'postal_code' => $address['postcode'] ?? null,
        ];
    }

    public function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2,
        string $unit = 'km'
    ): float {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        switch ($unit) {
            case 'mi':
                return round($miles, 2);
            case 'km':
                return round($miles * 1.609344, 2);
            case 'm':
                return round($miles * 1.609344 * 1000, 2);
            default:
                return round($miles * 1.609344, 2);
        }
    }

    public function findNearby(
        float $latitude,
        float $longitude,
        float $radiusKm,
        string $modelClass,
        ?callable $queryModifier = null
    ) {
        $degrees = $radiusKm / 111.32;

        $query = $modelClass::whereBetween('latitude', [$latitude - $degrees, $latitude + $degrees])
            ->whereBetween('longitude', [$longitude - $degrees, $longitude + $degrees]);

        if ($queryModifier) {
            $query = $queryModifier($query);
        }

        $results = $query->get();

        return $results->map(function ($item) use ($latitude, $longitude) {
            $item->distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $item->latitude,
                $item->longitude
            );

            return $item;
        })->filter(function ($item) use ($radiusKm) {
            return $item->distance <= $radiusKm;
        })->sortBy('distance')->values();
    }

    public function getBoundsFromCenter(float $latitude, float $longitude, float $radiusKm): array
    {
        $latChange = $radiusKm / 111.32;
        $lonChange = abs(cos($latitude * (pi() / 180)));
        $lonChange = $radiusKm / (111.32 * $lonChange);

        return [
            'north' => $latitude + $latChange,
            'south' => $latitude - $latChange,
            'east' => $longitude + $lonChange,
            'west' => $longitude - $lonChange,
        ];
    }

    public function isWithinBounds(float $latitude, float $longitude, array $bounds): bool
    {
        return $latitude >= $bounds['south']
            && $latitude <= $bounds['north']
            && $longitude >= $bounds['west']
            && $longitude <= $bounds['east'];
    }

    public function getUserLocation(): ?array
    {
        if (auth()->check() && auth()->user()->latitude && auth()->user()->longitude) {
            return [
                'latitude' => auth()->user()->latitude,
                'longitude' => auth()->user()->longitude,
                'city' => auth()->user()->city,
                'country' => auth()->user()->country,
            ];
        }

        return null;
    }
}
