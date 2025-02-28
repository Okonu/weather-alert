# Weather Alert Service

A Laravel-based application that notifies users about potentially harmful weather conditions through email alerts.

## Overview

Weather Alert Service monitors high precipitation levels and harmful UV index values for cities that users subscribe to. The service uses OpenWeatherMap API to fetch real-time weather data and sends email notifications when conditions exceed user-defined thresholds.

## Features (Tier 1)

- **Real-time Weather Monitoring**: View current weather conditions for any city
- **Email Alerts**: Receive notifications when precipitation or UV levels become dangerous
- **Custom Thresholds**: Default thresholds set for precipitation (5mm) and UV index (6)
- **Dashboard**: Built with Livewire
- **Setup**: Docker-based environment using Laravel Sail

## Technical Stack

- **Laravel**: PHP framework for backend
- **Livewire**: For interactive UI components
- **OpenWeatherMap API**: Weather data source
- **Laravel Notifications**: Email alert system
- **Laravel Sail**: Docker environment
- **Pest PHP**: Testing framework

## Getting Started

### Prerequisites

- Docker & Docker Compose
- OpenWeatherMap API key

### Installation

1. Clone the repository
   ```bash
   git clone https://github.com/Okonu/weather-alert.git
   cd weather-alert
   ```

2. Set up environment variables
   ```bash
   cp .env.example .env
   # Add your OpenWeatherMap API key to .env
   ```

3. Start the Docker environment
   ```bash
   ./vendor/bin/sail up -d
   ```

4. Run migrations
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

5. Visit http://localhost in your browser

## Usage

1. **View Weather**: Enter a city name in the dashboard to see current weather conditions
2. **Subscribe to Alerts**: Fill out the form with your email and city
3. **Receive Alerts**: Automated hourly checks will notify you when conditions exceed thresholds

## Testing

Run the test suite with:
```bash
./vendor/bin/sail artisan test
```

The tests cover:
- Weather data retrieval from API
- Alert threshold evaluation
- Email notification sending
- Livewire component functionality

## Scheduled Tasks

The system automatically checks weather conditions hourly. To run the scheduler:

```bash
./vendor/bin/sail artisan schedule:work
```

## Future Enhancements (Planned)

- User authentication and profiles
- Notification for multiple cities per user
- Custom threshold settings
- Additional notification channels
