<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeatherAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private array $cities;

    public function __construct(array $cities)
    {
        $this->cities = $cities;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $cityCount = count($this->cities);
        $cityWord = $cityCount === 1 ? 'City' : 'Cities';

        $mailMessage = (new MailMessage)
            ->subject("Weather Alert for " . $cityCount . " " . $cityWord)
            ->greeting("Hello!")
            ->line("We've detected potentially harmful weather conditions in " . $cityCount . " of your subscribed " . strtolower($cityWord) . ".");

        foreach ($this->cities as $city) {
            $mailMessage->line("**{$city['name']}**");

            foreach ($city['alerts'] as $alert) {
                if ($alert['type'] === 'precipitation') {
                    $mailMessage->line("⚠️ High Precipitation: {$alert['value']}mm (Threshold: {$alert['threshold']}mm)");
                } elseif ($alert['type'] === 'uv') {
                    $mailMessage->line("☀️ High UV Index: {$alert['value']} (Threshold: {$alert['threshold']})");
                }
            }

            if ($cityCount > 1) {
                $mailMessage->line("---");
            }
        }

        return $mailMessage
            ->line('Stay safe!')
            ->action('View Forecast', url('/'))
            ->line('Thank you for using our weather alert service!');
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function getCity(): string
    {
        return $this->cities[0]['name'] ?? '';
    }

    public function getAlerts(): array
    {
        return $this->cities[0]['alerts'] ?? [];
    }
}
