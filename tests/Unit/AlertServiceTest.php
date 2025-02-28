<?php

use App\Contracts\WeatherServiceInterface;
use App\Models\City;
use App\Models\User;
use App\Models\WeatherAlert;
use App\Notifications\WeatherAlertNotification;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('it sends notification for high precipitation', function () {
    Notification::fake();

    $weatherService = Mockery::mock(WeatherServiceInterface::class);
    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(4.0);

    $alert = WeatherAlert::factory()->create([
        'email' => 'test@example.com',
        'city' => 'London',
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertSentOnDemand(WeatherAlertNotification::class);

    $notification = new WeatherAlertNotification('London', [
        [
            'type' => 'precipitation',
            'value' => 10.0,
            'threshold' => 5.0
        ]
    ]);

    expect($notification->getCity())->toBe('London')
        ->and($notification->getAlerts())->toHaveCount(1)
        ->and($notification->getAlerts()[0]['type'])->toBe('precipitation')
        ->and($notification->getAlerts()[0]['value'])->toBe(10.0);
});

test('it sends notification for high UV index', function () {
    Notification::fake();

    $weatherService = Mockery::mock(WeatherServiceInterface::class);
    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(2.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(8.0);

    $alert = WeatherAlert::factory()->create([
        'email' => 'test@example.com',
        'city' => 'London',
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertSentOnDemand(WeatherAlertNotification::class);

    $notification = new WeatherAlertNotification('London', [
        [
            'type' => 'uv',
            'value' => 8.0,
            'threshold' => 6.0
        ]
    ]);

    expect($notification->getCity())->toBe('London')
        ->and($notification->getAlerts())->toHaveCount(1)
        ->and($notification->getAlerts()[0]['type'])->toBe('uv')
        ->and($notification->getAlerts()[0]['value'])->toBe(8.0);
});

test('it does not send notification when conditions are normal', function () {
    Notification::fake();

    $weatherService = Mockery::mock(WeatherServiceInterface::class);
    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(2.0);
    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(4.0);

    $alert = WeatherAlert::factory()->create([
        'email' => 'test@example.com',
        'city' => 'London',
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertNothingSent();
});

test('it respects alert settings', function () {
    Notification::fake();

    $weatherService = Mockery::mock(WeatherServiceInterface::class);
    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(8.0);

    $alert = WeatherAlert::factory()->create([
        'email' => 'test@example.com',
        'city' => 'London',
        'precipitation_enabled' => false,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertSentOnDemand(WeatherAlertNotification::class);

    $notification = new WeatherAlertNotification('London', [
        [
            'type' => 'uv',
            'value' => 8.0,
            'threshold' => 6.0
        ]
    ]);

    expect($notification->getCity())->toBe('London')
        ->and($notification->getAlerts())->toHaveCount(1)
        ->and($notification->getAlerts()[0]['type'])->toBe('uv');
});

test('it sends notification for multiple cities to authenticated user', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'user@example.com']);
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

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(4.0);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('Paris')
        ->andReturn(2.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('Paris')
        ->andReturn(8.0);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertSentTo(
        $user,
        WeatherAlertNotification::class,
        function ($notification) {
            $cities = $notification->getCities();

            return count($cities) === 2 &&
                $cities[0]['name'] === 'London' &&
                $cities[0]['alerts'][0]['type'] === 'precipitation' &&
                $cities[1]['name'] === 'Paris' &&
                $cities[1]['alerts'][0]['type'] === 'uv';
        }
    );
});

test('it uses custom thresholds for each city', function () {
    Notification::fake();

    $user = User::factory()->create();
    $london = City::factory()->create(['name' => 'London']);
    $paris = City::factory()->create(['name' => 'Paris']);

    $user->cities()->attach($london->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 10.0,
        'uv_threshold' => 6.0,
    ]);

    $user->cities()->attach($paris->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $weatherService = Mockery::mock(WeatherServiceInterface::class);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(7.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(4.0);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('Paris')
        ->andReturn(7.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('Paris')
        ->andReturn(4.0);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertSentTo(
        $user,
        WeatherAlertNotification::class,
        function ($notification) {
            $cities = $notification->getCities();

            return count($cities) === 1 &&
                $cities[0]['name'] === 'Paris' &&
                $cities[0]['alerts'][0]['type'] === 'precipitation';
        }
    );
});

test('it handles different alert settings for each city', function () {
    Notification::fake();

    $user = User::factory()->create();
    $london = City::factory()->create(['name' => 'London']);
    $paris = City::factory()->create(['name' => 'Paris']);

    $user->cities()->attach($london->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => false,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $user->cities()->attach($paris->id, [
        'precipitation_enabled' => false,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $weatherService = Mockery::mock(WeatherServiceInterface::class);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(8.0);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('Paris')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('Paris')
        ->andReturn(8.0);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertSentTo(
        $user,
        WeatherAlertNotification::class,
        function ($notification) {
            $cities = $notification->getCities();

            if (count($cities) !== 2) return false;

            $londonAlerts = collect($cities)->firstWhere('name', 'London')['alerts'];
            $parisAlerts = collect($cities)->firstWhere('name', 'Paris')['alerts'];

            return count($londonAlerts) === 1 &&
                $londonAlerts[0]['type'] === 'precipitation' &&
                count($parisAlerts) === 1 &&
                $parisAlerts[0]['type'] === 'uv';
        }
    );
});

test('it handles both legacy and authenticated user alerts', function () {
    Notification::fake();

    $legacyAlert = WeatherAlert::factory()->create([
        'email' => 'legacy@example.com',
        'city' => 'Berlin',
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'Rome']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $weatherService = Mockery::mock(WeatherServiceInterface::class);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('Berlin')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('Berlin')
        ->andReturn(4.0);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('Rome')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('Rome')
        ->andReturn(4.0);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertSentTo(
        [['mail' => 'legacy@example.com']],
        WeatherAlertNotification::class
    );

    Notification::assertSentTo(
        $user,
        WeatherAlertNotification::class
    );
});

test('it sends no notification when all alerts are disabled', function () {
    Notification::fake();

    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => false,
        'uv_enabled' => false,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    $weatherService = Mockery::mock(WeatherServiceInterface::class);

    $weatherService->shouldReceive('getPrecipitation')
        ->with('London')
        ->andReturn(10.0);

    $weatherService->shouldReceive('getUvIndex')
        ->with('London')
        ->andReturn(8.0);

    $alertService = new AlertService($weatherService);
    $alertService->processAlerts();

    Notification::assertNotSentTo($user, WeatherAlertNotification::class);
});
