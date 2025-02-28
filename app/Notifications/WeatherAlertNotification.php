<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeatherAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $city;

    private array $alerts;

    public function __construct(string $city, array $alerts)
    {
        $this->city = $city;
        $this->alerts = $alerts;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject("Weather Alert for {$this->city}")
            ->greeting("Hello!")
            ->line("We've detected potentially harmful weather conditions in {$this->city}.");

        foreach ($this->alerts as $alert) {
            if ($alert['type'] === 'precipitation') {
                $mailMessage->line("⚠️ High Precipitation: {$alert['value']}mm (Threshold: {$alert['threshold']}mm)");
            } elseif ($alert['type'] === 'uv') {
                $mailMessage->line("☀️ High UV Index: {$alert['value']} (Threshold: {$alert['threshold']})");
            }
        }

        return $mailMessage
            ->line('Stay safe!')
            ->action('View Forecast', url('/'))
            ->line('Thank you for using our weather alert service!');
    }
}
