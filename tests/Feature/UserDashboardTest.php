<?php

use App\Contracts\WeatherServiceInterface;
use App\Livewire\UserDashboard;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('dashboard screen can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});

test('dashboard shows user cities weather', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

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

    $response = Livewire::actingAs($user)
        ->test(UserDashboard::class);

    $response->assertSee('London')
        ->assertSee('15.5')
        ->assertSee('Clear sky', false)
        ->assertSee('2.5')
        ->assertSee('4.0');
});

test('dashboard shows warning for high values', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

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

    Livewire::actingAs($user)
        ->test(UserDashboard::class)
        ->assertSee('London')
        ->assertSee('8.5mm')
        ->assertSee('7.0')
        ->assertSee('⚠️ High')
        ->assertSee('⚠️ Harmful');
});

test('dashboard shows multiple cities', function () {
    $user = User::factory()->create();
    $london = City::factory()->create(['name' => 'London']);
    $paris = City::factory()->create(['name' => 'Paris']);

    $user->cities()->attach($london->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $user->cities()->attach($paris->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

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
        ->andReturn(1.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('Paris')
        ->andReturn(5.0);

    $this->app->instance(WeatherServiceInterface::class, $weatherService);

    $response = Livewire::actingAs($user)
        ->test(UserDashboard::class);

    $citiesWeather = $response->get('citiesWeather');

    expect($citiesWeather)->toBeArray()
        ->and($citiesWeather)->toHaveCount(2)
        ->and($citiesWeather[0]['name'])->toBe('London')
        ->and($citiesWeather[0]['temperature'])->toBe(15.5)
        ->and($citiesWeather[1]['name'])->toBe('Paris')
        ->and($citiesWeather[1]['temperature'])->toBe(18.0);
});

test('dashboard respects custom thresholds', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 10.0,
        'uv_threshold' => 9.0,
    ]);

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

    Livewire::actingAs($user)
        ->test(UserDashboard::class)
        ->assertSee('London')
        ->assertSee('8.5mm')
        ->assertSee('7.0')
        ->assertDontSee('⚠️ High')
        ->assertDontSee('⚠️ Harmful');
});

test('dashboard reflects disabled alert types', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => false,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $weatherService = Mockery::mock(WeatherServiceInterface::class);
    $weatherService->shouldReceive('getCurrentWeather')
        ->with('London')
        ->andReturn([
            'main' => ['temp' => 15.5],
            'weather' => [['description' => 'sunny']]
        ]);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(2.5);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(9.0);

    $this->app->instance(WeatherServiceInterface::class, $weatherService);

    Livewire::actingAs($user)
        ->test(UserDashboard::class)
        ->assertSee('London')
        ->assertSee('9.0')
        ->assertDontSee('⚠️ Harmful');
});

test('refresh weather button works', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $weatherService = Mockery::mock(WeatherServiceInterface::class);

    $weatherService->shouldReceive('getCurrentWeather')
        ->with('London')
        ->once()
        ->andReturn([
            'main' => ['temp' => 15.5],
            'weather' => [['description' => 'clear sky']]
        ]);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->once()
        ->andReturn(2.5);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->once()
        ->andReturn(4.0);

    $weatherService->shouldReceive('getCurrentWeather')
        ->with('London')
        ->once()
        ->andReturn([
            'main' => ['temp' => 16.0],
            'weather' => [['description' => 'cloudy']]
        ]);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->once()
        ->andReturn(3.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->once()
        ->andReturn(4.5);

    $this->app->instance(WeatherServiceInterface::class, $weatherService);

    $response = Livewire::actingAs($user)
        ->test(UserDashboard::class);

    $initialCitiesWeather = $response->get('citiesWeather');
    expect($initialCitiesWeather[0]['temperature'])->toBe(15.5);

    $response->call('refreshWeather');
    $refreshedCitiesWeather = $response->get('citiesWeather');

    expect($refreshedCitiesWeather[0]['temperature'])->toBe(16.0);
});
