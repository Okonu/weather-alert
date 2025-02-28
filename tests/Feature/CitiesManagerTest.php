<?php

use App\Livewire\CitiesManager;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('cities manager screen can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/cities')
        ->assertStatus(200);
});

test('cities can be searched', function () {
    $user = User::factory()->create();
    City::factory()->create(['name' => 'London']);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('cityName', 'London')
        ->call('searchCity')
        ->assertSee('London');
});

test('user can subscribe to city', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('cityName', 'London')
        ->call('searchCity')
        ->call('selectCity', $city->id)
        ->set('precipitation_threshold', 7.5)
        ->set('uv_threshold', 8.0)
        ->call('subscribeToCity')
        ->assertSee('You\'ve subscribed to London alerts!');

    expect($user->cities()->where('name', 'London')->exists())->toBeTrue()
        ->and($user->cities()->where('name', 'London')->first()->pivot->precipitation_threshold)->toBe(7.5)
        ->and($user->cities()->where('name', 'London')->first()->pivot->uv_threshold)->toBe(8.0);
});

test('user can unsubscribe from city', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->call('unsubscribeFromCity', $city->id)
        ->assertSee('City removed from your subscriptions.');

    expect($user->cities()->where('name', 'London')->exists())->toBeFalse();
});

test('user can toggle alert types', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('cityName', 'London')
        ->call('searchCity')
        ->call('selectCity', $city->id)
        ->set('precipitation_enabled', true)
        ->set('uv_enabled', false)
        ->set('precipitation_threshold', 5.0)
        ->set('uv_threshold', 6.0)
        ->call('subscribeToCity');

    $userCity = $user->fresh()->cities()->where('name', 'London')->first();

    expect($userCity)->not()->toBeNull()
        ->and((bool)$userCity->pivot->precipitation_enabled)->toBeTrue()
        ->and((bool)$userCity->pivot->uv_enabled)->toBeFalse();
});

test('user can subscribe to multiple cities', function () {
    $user = User::factory()->create();
    $city1 = City::factory()->create(['name' => 'London']);
    $city2 = City::factory()->create(['name' => 'Paris']);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('cityName', 'London')
        ->call('searchCity')
        ->call('selectCity', $city1->id)
        ->call('subscribeToCity');

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('cityName', 'Paris')
        ->call('searchCity')
        ->call('selectCity', $city2->id)
        ->call('subscribeToCity');

    expect($user->cities()->count())->toBe(2);
});

test('user can update threshold settings', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    $user->cities()->attach($city->id, [
        'precipitation_enabled' => true,
        'uv_enabled' => true,
        'precipitation_threshold' => 5.0,
        'uv_threshold' => 6.0,
    ]);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('precipitation_threshold', 10.0)
        ->set('uv_threshold', 9.0)
        ->set('precipitation_enabled', true)
        ->set('uv_enabled', false)
        ->call('updateThresholds', $city->id);

    $updatedCity = $user->fresh()->cities()->where('name', 'London')->first();

    expect($updatedCity->pivot->precipitation_threshold)->toBe(10.0)
        ->and($updatedCity->pivot->uv_threshold)->toBe(9.0)
        ->and((bool)$updatedCity->pivot->precipitation_enabled)->toBeTrue()
        ->and((bool)$updatedCity->pivot->uv_enabled)->toBeFalse();
});

test('validation rules are enforced', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'London']);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('cityName', 'L')
        ->call('searchCity')
        ->assertHasErrors(['cityName' => 'min']);

    Livewire::actingAs($user)
        ->test(CitiesManager::class)
        ->set('cityName', 'London')
        ->call('searchCity')
        ->call('selectCity', $city->id)
        ->set('precipitation_threshold', -1)
        ->call('subscribeToCity')
        ->assertHasErrors(['precipitation_threshold' => 'min']);
});
