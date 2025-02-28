<?php

namespace Database\Factories;

use App\Models\WeatherAlert;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

class WeatherAlertFactory extends Factory
{
    protected $model = WeatherAlert::class;

    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
            'city' => fake()->city(),
            'precipitation_enabled' => true,
            'uv_enabled' => true,
            'precipitation_threshold' => 5.0,
            'uv_threshold' => 6.0,
        ];
    }
}
