<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'lat' => $this->faker->latitude(),
            'lon' => $this->faker->longitude(),
        ];
    }
}
