<?php

use App\Contracts\WeatherServiceInterface;
use App\Livewire\WeatherDashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('weather dashboard can be rendered', function () {
    $this->get('/')
        ->assertStatus(200)
        ->assertSeeLivewire('weather-dashboard');
});

test('dashboard shows weather data', function () {
    $weatherService = Mockery::mock(WeatherServiceInterface::class);
    $weatherService->shouldReceive('getCurrentWeather')
        ->with('London')
        ->andReturn([
            'main' => ['temp' => 15.5],
            'weather' => [['description' => 'clear sky']]
        ]);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(2.5);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(4.0);

    $this->app->instance(WeatherServiceInterface::class, $weatherService);

    Livewire::test(WeatherDashboard::class)
        ->assertSee('Current Weather')
        ->assertSee('Temperature: 15.5°C')
        ->assertSee('Conditions: Clear sky')
        ->assertSee('2.5mm')
        ->assertSee('4')
        ->assertDontSee('High')
        ->assertDontSee('Harmful');
});

test('dashboard shows warnings for high values', function () {
    $weatherService = Mockery::mock(WeatherServiceInterface::class);
    $weatherService->shouldReceive('getCurrentWeather')
        ->with('London')
        ->andReturn([
            'main' => ['temp' => 15.5],
            'weather' => [['description' => 'rain']]
        ]);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(8.5);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(7.0);

    $this->app->instance(WeatherServiceInterface::class, $weatherService);

    Livewire::test(WeatherDashboard::class)
        ->assertSee('Current Weather')
        ->assertSee('8.5mm')
        ->assertSee('7')
        ->assertSee('High')
        ->assertSee('Harmful');
});

test('user can change city', function () {
    $weatherService = Mockery::mock(WeatherServiceInterface::class);

    $weatherService->shouldReceive('getCurrentWeather')
        ->with('London')
        ->andReturn([
            'main' => ['temp' => 15.5],
            'weather' => [['description' => 'clear sky']]
        ]);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(2.5);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(4.0);

    $weatherService->shouldReceive('getCurrentWeather')
        ->with('Paris')
        ->andReturn([
            'main' => ['temp' => 18.0],
            'weather' => [['description' => 'partly cloudy']]
        ]);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('Paris')
        ->andReturn(0.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('Paris')
        ->andReturn(5.0);

    $this->app->instance(WeatherServiceInterface::class, $weatherService);

    Livewire::test(WeatherDashboard::class)
        ->assertSee('Temperature: 15.5°C')
        ->set('city', 'Paris')
        ->call('checkWeather')
        ->assertSee('Temperature: 18°C')
        ->assertSee('Conditions: Partly cloudy');
});
