<?php

namespace App\Livewire;

use App\Models\WeatherAlert;
use Livewire\Component;

class WeatherAlertForm extends Component
{
    public $email = '';
    public $city = '';
    public $precipitation_enabled = true;
    public $uv_enabled = true;

    protected $rules = [
        'email' => 'required|email',
        'city' => 'required|string|min:2|max:100',
        'precipitation_enabled' => 'boolean',
        'uv_enabled' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.weather-alert-form');
    }

    public function submit()
    {
        $this->validate();

        WeatherAlert::create([
            'email' => $this->email,
            'city' => $this->city,
            'precipitation_enabled' => $this->precipitation_enabled,
            'uv_enabled' => $this->uv_enabled,
            'precipitation_threshold' => 5.0,
            'uv_threshold' => 6.0,
        ]);

        session()->flash('message', 'Weather alert subscription created successfully!');

        $this->dispatch('alert-created');

        $this->reset(['email', 'city']);
    }
}
