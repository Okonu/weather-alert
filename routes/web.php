<?php

use App\Livewire\CitiesManager;
use App\Livewire\UserDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', UserDashboard::class)->name('dashboard');

    Route::get('/cities', CitiesManager::class)->name('cities.manage');
});
