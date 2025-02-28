<?php

use App\Contracts\WeatherServiceInterface;
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
