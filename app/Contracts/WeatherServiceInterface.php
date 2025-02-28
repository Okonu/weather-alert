<?php

namespace App\Contracts;

interface WeatherServiceInterface
{
    public function getCurrentWeather(string $city): array;

    public function getPrecipitation(string $city): float;

    public function getUvIndex(string $city): float;
}
