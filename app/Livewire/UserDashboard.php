<?php

namespace App\Livewire;

use App\Contracts\WeatherServiceInterface;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserDashboard extends Component
{
    public $citiesWeather = [];
    public $loading = false;

    public function mount()
    {
        $this->refreshWeather();
    }

    public function render()
    {
        return view('livewire.user-dashboard');
    }

    public function refreshWeather()
    {
        $this->loading = true;
        $weatherService = app(WeatherServiceInterface::class);

        $this->citiesWeather = [];
        $user = Auth::user();

        if ($user && $user->cities) {
            foreach ($user->cities as $city) {
                try {
                    $weather = $weatherService->getCurrentWeather($city->name);
                    $precipitation = $weatherService->getPrecipitation($city->name);
                    $uvIndex = $weatherService->getUvIndex($city->name);

                    $this->citiesWeather[] = [
                        'id' => $city->id,
                        'name' => $city->name,
                        'temperature' => $weather['main']['temp'] ?? null,
                        'description' => $weather['weather'][0]['description'] ?? null,
                        'precipitation' => $precipitation,
                        'uvIndex' => $uvIndex,
                        'precipitationWarning' => $precipitation >= $city->pivot->precipitation_threshold && $city->pivot->precipitation_enabled,
                        'uvWarning' => $uvIndex >= $city->pivot->uv_threshold && $city->pivot->uv_enabled,
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        $this->loading = false;
    }
}
