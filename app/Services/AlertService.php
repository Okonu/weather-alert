<?php

namespace App\Services;

use App\Contracts\WeatherServiceInterface;
use App\Models\User;
use App\Notifications\WeatherAlertNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AlertService
{
    private WeatherServiceInterface $weatherService;

    public function __construct(WeatherServiceInterface $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function processAlerts(): void
    {
        $users = User::with('cities')->get();

        foreach ($users as $user) {
            $this->processUserAlerts($user);
        }

        $this->processLegacyAlerts();
    }

    protected function processUserAlerts(User $user): void
    {
        $triggeredCities = [];

        foreach ($user->cities as $city) {
            $cityAlerts = [];

            if ($city->pivot->precipitation_enabled) {
                $precipitation = $this->weatherService->getPrecipitation($city->name);

                if ($precipitation >= $city->pivot->precipitation_threshold) {
                    $cityAlerts[] = [
                        'type' => 'precipitation',
                        'value' => $precipitation,
                        'threshold' => $city->pivot->precipitation_threshold
                    ];
                }
            }

            if ($city->pivot->uv_enabled) {
                $uvIndex = $this->weatherService->getUvIndex($city->name);

                if ($uvIndex >= $city->pivot->uv_threshold) {
                    $cityAlerts[] = [
                        'type' => 'uv',
                        'value' => $uvIndex,
                        'threshold' => $city->pivot->uv_threshold
                    ];
                }
            }

            if (!empty($cityAlerts)) {
                $triggeredCities[] = [
                    'name' => $city->name,
                    'alerts' => $cityAlerts
                ];
            }
        }

        if (!empty($triggeredCities)) {
            try {
                $user->notify(new WeatherAlertNotification($triggeredCities));

                Log::info('Weather alerts sent to user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'cities' => $triggeredCities
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send weather alerts to user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'exception' => $e->getMessage()
                ]);
            }
        }
    }

    protected function processLegacyAlerts(): void
    {
        $alerts = \App\Models\WeatherAlert::whereNull('user_id')->get();

        foreach ($alerts as $alert) {
            $triggeredAlerts = [];
            if ($alert->precipitation_enabled) {
                $precipitation = $this->weatherService->getPrecipitation($alert->city);

                if ($precipitation >= $alert->precipitation_threshold) {
                    $triggeredAlerts[] = [
                        'type' => 'precipitation',
                        'value' => $precipitation,
                        'threshold' => $alert->precipitation_threshold
                    ];
                }
            }

            if ($alert->uv_enabled) {
                $uvIndex = $this->weatherService->getUvIndex($alert->city);

                if ($uvIndex >= $alert->uv_threshold) {
                    $triggeredAlerts[] = [
                        'type' => 'uv',
                        'value' => $uvIndex,
                        'threshold' => $alert->uv_threshold
                    ];
                }
            }

            if (!empty($triggeredAlerts)) {
                try {
                    $cityData = [
                        [
                            'name' => $alert->city,
                            'alerts' => $triggeredAlerts
                        ]
                    ];

                    Notification::route('mail', $alert->email)
                        ->notify(new WeatherAlertNotification($cityData));

                    Log::info('Legacy weather alert sent', [
                        'email' => $alert->email,
                        'city' => $alert->city,
                        'alerts' => $triggeredAlerts
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send legacy weather alert', [
                        'email' => $alert->email,
                        'city' => $alert->city,
                        'exception' => $e->getMessage()
                    ]);
                }
            }
        }
    }
}
