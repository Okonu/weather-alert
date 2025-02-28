<?php

use App\Contracts\WeatherServiceInterface;
use App\Services\OpenWeatherMapService;
use Illuminate\Support\Facades\Http;

test('it can fetch current weather', function () {
    $this->instance(
        WeatherServiceInterface::class,
        new OpenWeatherMapService()
    );

    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response([
            'coord' => ['lat' => 51.51, 'lon' => -0.13],
            'weather' => [['id' => 800, 'main' => 'Clear', 'description' => 'clear sky']],
            'main' => ['temp' => 15.5],
            'rain' => ['1h' => 0]
        ], 200)
    ]);

    $service = app(WeatherServiceInterface::class);
    $weather = $service->getCurrentWeather('London');

    expect($weather)->toBeArray()
        ->and($weather)->toHaveKey('weather')
        ->and($weather['weather'][0]['main'])->toBe('Clear');
});

test('it returns precipitation amount', function () {
    $service = Mockery::mock(OpenWeatherMapService::class);
    $service->makePartial();
    $service->shouldReceive('getCurrentWeather')
        ->with('London')
        ->andReturn([
            'rain' => ['3h' => 7.5]
        ]);

    $precipitation = $service->getPrecipitation('London');

    expect($precipitation)->toBe(7.5);
});

test('it returns zero precipitation when no rain data', function () {
    $service = Mockery::mock(OpenWeatherMapService::class);
    $service->makePartial();
    $service->shouldReceive('getCurrentWeather')
        ->with('London')
        ->andReturn([
            'weather' => [['main' => 'Clear']]
        ]);

    $precipitation = $service->getPrecipitation('London');

    expect($precipitation)->toEqual(0.0);
});
