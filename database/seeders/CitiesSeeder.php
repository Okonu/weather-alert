<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'London', 'country' => 'GB', 'lat' => 51.5074, 'lon' => -0.1278],
            ['name' => 'New York', 'country' => 'US', 'lat' => 40.7128, 'lon' => -74.0060],
            ['name' => 'Tokyo', 'country' => 'JP', 'lat' => 35.6762, 'lon' => 139.6503],
            ['name' => 'Sydney', 'country' => 'AU', 'lat' => -33.8688, 'lon' => 151.2093],
            ['name' => 'Paris', 'country' => 'FR', 'lat' => 48.8566, 'lon' => 2.3522],
            ['name' => 'Berlin', 'country' => 'DE', 'lat' => 52.5200, 'lon' => 13.4050],
            ['name' => 'Rome', 'country' => 'IT', 'lat' => 41.9028, 'lon' => 12.4964],
            ['name' => 'Cairo', 'country' => 'EG', 'lat' => 30.0444, 'lon' => 31.2357],
            ['name' => 'Rio de Janeiro', 'country' => 'BR', 'lat' => -22.9068, 'lon' => -43.1729],
            ['name' => 'Mumbai', 'country' => 'IN', 'lat' => 19.0760, 'lon' => 72.8777],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['name' => $city['name'], 'country' => $city['country']],
                $city
            );
        }
    }
}
