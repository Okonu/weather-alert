<?php

namespace App\Services;

use App\Contracts\WeatherServiceInterface;
use App\Models\WeatherAlert;
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
        $alerts = WeatherAlert::all();

        foreach ($alerts as $alert) {
            $this->checkAlert($alert);
        }
    }

    protected function checkAlert(WeatherAlert $alert): void
    {
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
                Notification::route('mail', $alert->email)
                    ->notify(new WeatherAlertNotification($alert->city, $triggeredAlerts));

                Log::info('Weather alert sent', [
                    'email' => $alert->email,
                    'city' => $alert->city,
                    'alerts' => $triggeredAlerts
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send weather alert', [
                    'email' => $alert->email,
                    'city' => $alert->city,
                    'exception' => $e->getMessage()
                ]);
            }
        }
    }
}
