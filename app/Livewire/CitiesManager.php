<?php

namespace App\Livewire;

use App\Models\City;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CitiesManager extends Component
{
    public $cityName = '';
    public $selectedCity = null;
    public $precipitation_enabled = true;
    public $uv_enabled = true;
    public $precipitation_threshold = 5.0;
    public $uv_threshold = 6.0;

    public $cities = [];
    public $userCities = [];

    protected $rules = [
        'cityName' => 'required|string|min:2',
        'precipitation_threshold' => 'required|numeric|min:0.1',
        'uv_threshold' => 'required|numeric|min:1',
    ];

    public function mount()
    {
        $this->refreshUserCities();
    }

    public function render()
    {
        return view('livewire.cities-manager');
    }

    public function searchCity()
    {
        $this->validate([
            'cityName' => 'required|string|min:2',
        ]);

        $this->cities = City::where('name', 'like', "%{$this->cityName}%")
            ->take(5)
            ->get();

        if ($this->cities->isEmpty()) {
            $city = City::create([
                'name' => $this->cityName,
            ]);

            $this->cities = collect([$city]);
        }
    }

    public function selectCity($cityId)
    {
        $this->selectedCity = City::find($cityId);
    }

    public function subscribeToCity()
    {
        $this->validate();

        if (!$this->selectedCity) {
            session()->flash('error', 'Please select a city first.');
            return;
        }

        Auth::user()->cities()->attach($this->selectedCity->id, [
            'precipitation_enabled' => $this->precipitation_enabled,
            'uv_enabled' => $this->uv_enabled,
            'precipitation_threshold' => $this->precipitation_threshold,
            'uv_threshold' => $this->uv_threshold,
        ]);

        session()->flash('message', "You've subscribed to {$this->selectedCity->name} alerts!");

        $this->reset(['cityName', 'selectedCity', 'cities']);
        $this->refreshUserCities();
    }

    public function unsubscribeFromCity($cityId)
    {
        Auth::user()->cities()->detach($cityId);
        session()->flash('message', 'City removed from your subscriptions.');

        $this->refreshUserCities();
    }

    public function updateThresholds($cityId)
    {
        $city = Auth::user()->cities()->find($cityId);

        if ($city) {
            Auth::user()->cities()->updateExistingPivot($cityId, [
                'precipitation_threshold' => $this->precipitation_threshold,
                'uv_threshold' => $this->uv_threshold,
                'precipitation_enabled' => $this->precipitation_enabled,
                'uv_enabled' => $this->uv_enabled,
            ]);

            session()->flash('message', 'Alert settings updated successfully!');
            $this->refreshUserCities();
        }
    }

    private function refreshUserCities()
    {
        $this->userCities = Auth::user()->cities()->get();
    }
}
