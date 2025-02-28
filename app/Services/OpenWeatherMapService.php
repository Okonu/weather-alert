<?php

namespace App\Services;

use App\Contracts\WeatherServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenWeatherMapService implements WeatherServiceInterface
{
    private string $apiKey;

    private int $cacheDuration = 30;

    public function __construct()
    {
        $this->apiKey = config('services.openweathermap.key');
    }

    public function getCurrentWeather(string $city): array
    {
        $cacheKey = "weather_{$city}";

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheDuration), function () use ($city) {
            try {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Weather API error', [
                    'city' => $city,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Weather service exception', [
                    'city' => $city,
                    'exception' => $e->getMessage()
                ]);

                return [];
            }
        });
    }

    public function getPrecipitation(string $city): float
    {
        $weather = $this->getCurrentWeather($city);

        return $weather['rain']['1h'] ?? $weather['rain']['3h'] ?? 0;
    }

    public function getUvIndex(string $city): float
    {
        $cacheKey = "uv_{$city}";

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheDuration), function () use ($city) {
            try {
                $weather = $this->getCurrentWeather($city);

                if (empty($weather) || !isset($weather['coord'])) {
                    return 0;
                }

                $lat = $weather['coord']['lat'];
                $lon = $weather['coord']['lon'];

                $response = Http::get('https://api.openweathermap.org/data/2.5/uvi', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json()['value'] ?? 0;
                }

                return 0;
            } catch (\Exception $e) {
                Log::error('UV service exception', [
                    'city' => $city,
                    'exception' => $e->getMessage()
                ]);

                return 0;
            }
        });
    }
}
