<?php

namespace App\Livewire;

use App\Contracts\WeatherServiceInterface;
use Livewire\Component;

class WeatherDashboard extends Component
{
    public $city = 'London';
    public $weatherData = null;
    public $loading = false;

    protected $listeners = ['checkWeather'];

    public function mount()
    {
        $this->checkWeather();
    }

    public function render()
    {
        return view('livewire.weather-dashboard');
    }

    public function checkWeather()
    {
        $this->loading = true;

        $weatherService = app(WeatherServiceInterface::class);

        try {
            $weather = $weatherService->getCurrentWeather($this->city);
            $precipitation = $weatherService->getPrecipitation($this->city);
            $uvIndex = $weatherService->getUvIndex($this->city);

            $this->weatherData = [
                'city' => $this->city,
                'temperature' => $weather['main']['temp'] ?? null,
                'description' => $weather['weather'][0]['description'] ?? null,
                'precipitation' => $precipitation,
                'uvIndex' => $uvIndex,
                'precipitationWarning' => $precipitation >= 5.0,
                'uvWarning' => $uvIndex >= 6.0,
            ];
        } catch (\Exception $e) {
            session()->flash('error', 'Error fetching weather data. Please try again.');
            $this->weatherData = null;
        }

        $this->loading = false;
    }

    public function updatedCity()
    {
        $this->checkWeather();
    }
}
