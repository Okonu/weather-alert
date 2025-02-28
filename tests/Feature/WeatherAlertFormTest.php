<?php

use App\Livewire\WeatherAlertForm;
use App\Models\WeatherAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('weather alert form can be rendered', function () {
    Livewire::test(WeatherAlertForm::class)
        ->assertStatus(200)
        ->assertSee('Subscribe to Weather Alerts');
});

test('weather alert form creates a subscription', function () {
    Livewire::test(WeatherAlertForm::class)
        ->set('email', 'test@example.com')
        ->set('city', 'London')
        ->set('precipitation_enabled', true)
        ->set('uv_enabled', true)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertDispatched('alert-created');

    $this->assertDatabaseHas('weather_alerts', [
        'email' => 'test@example.com',
        'city' => 'London',
        'precipitation_enabled' => true,
        'uv_enabled' => true
    ]);
});

test('email is required', function () {
    Livewire::test(WeatherAlertForm::class)
        ->set('email', '')
        ->set('city', 'London')
        ->call('submit')
        ->assertHasErrors(['email' => 'required']);
});

test('city is required', function () {
    Livewire::test(WeatherAlertForm::class)
        ->set('email', 'test@example.com')
        ->set('city', '')
        ->call('submit')
        ->assertHasErrors(['city' => 'required']);
});

test('email must be valid', function () {
    Livewire::test(WeatherAlertForm::class)
        ->set('email', 'not-an-email')
        ->set('city', 'London')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);
});

test('form resets after successful submission', function () {
    Livewire::test(WeatherAlertForm::class)
        ->set('email', 'test@example.com')
        ->set('city', 'London')
        ->call('submit')
        ->assertSet('email', '')
        ->assertSet('city', '');
});
