<?php

use App\Notifications\WeatherAlertNotification;

test('it generates correct mail for single city', function () {
    $cities = [
        [
            'name' => 'London',
            'alerts' => [
                [
                    'type' => 'precipitation',
                    'value' => 10.0,
                    'threshold' => 5.0
                ]
            ]
        ]
    ];

    $notification = new WeatherAlertNotification($cities, null, 'http://test.com');
    $mail = $notification->toMail('test@example.com');

    expect($mail->subject)->toBe('Weather Alert for 1 City')
        ->and($mail->introLines)->toBeArray()
        ->and($mail->introLines[1])->toContain('London')
        ->and($mail->introLines[2])->toContain('High Precipitation: 10mm')
        ->and($mail->actionUrl)->toBe('http://test.com');
});

test('it generates correct mail for multiple cities', function () {
    $cities = [
        [
            'name' => 'London',
            'alerts' => [
                [
                    'type' => 'precipitation',
                    'value' => 10.0,
                    'threshold' => 5.0
                ]
            ]
        ],
        [
            'name' => 'Paris',
            'alerts' => [
                [
                    'type' => 'uv',
                    'value' => 8.0,
                    'threshold' => 6.0
                ]
            ]
        ]
    ];

    $notification = new WeatherAlertNotification($cities, null, 'http://test.com');
    $mail = $notification->toMail('test@example.com');

    expect($mail->subject)->toBe('Weather Alert for 2 Cities')
        ->and($mail->introLines)->toBeArray()
        ->and($mail->introLines[1])->toContain('London')
        ->and($mail->introLines[2])->toContain('High Precipitation: 10mm')
        ->and($mail->introLines[4])->toContain('Paris')
        ->and($mail->introLines[5])->toContain('High UV Index: 8')
        ->and($mail->actionUrl)->toBe('http://test.com');
});

test('it includes multiple alerts for same city', function () {
    $cities = [
        [
            'name' => 'London',
            'alerts' => [
                [
                    'type' => 'precipitation',
                    'value' => 10.0,
                    'threshold' => 5.0
                ],
                [
                    'type' => 'uv',
                    'value' => 8.0,
                    'threshold' => 6.0
                ]
            ]
        ]
    ];

    $notification = new WeatherAlertNotification($cities);
    $mail = $notification->toMail('test@example.com');

    expect($mail->introLines)->toBeArray()
        ->and($mail->introLines[2])->toContain('High Precipitation: 10mm')
        ->and($mail->introLines[3])->toContain('High UV Index: 8');
});

test('get city and get alerts provide backward compatibility', function () {
    $cities = [
        [
            'name' => 'London',
            'alerts' => [
                [
                    'type' => 'precipitation',
                    'value' => 10.0,
                    'threshold' => 5.0
                ]
            ]
        ]
    ];

    $notification = new WeatherAlertNotification($cities);

    expect($notification->getCity())->toBe('London')
        ->and($notification->getAlerts())->toBe($cities[0]['alerts']);
});
