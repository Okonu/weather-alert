# Weather Alert Service

A Laravel-based application that notifies users about potentially harmful weather conditions through email alerts.

## Overview

Weather Alert Service monitors high precipitation levels and harmful UV index values for cities that users subscribe to. The service uses OpenWeatherMap API to fetch real-time weather data and sends email notifications when conditions exceed user-defined thresholds.

## Features

### Tier 1
- **Real-time Weather Monitoring**: View current weather conditions for any city
- **Email Alerts**: Receive notifications when precipitation or UV levels become dangerous
- **Basic Alert Settings**: Configure which types of alerts you receive
- **Docker-based Setup**: One-button deployment with Laravel Sail

### Tier 2
- **User Authentication**: Register and manage your account with Laravel Jetstream
- **Multiple Cities**: Subscribe to alerts for multiple cities of interest
- **Custom Thresholds**: Set different alert thresholds for each city
- **Personalized Dashboard**: View current conditions for all your subscribed cities
- **Advanced Alert Configuration**: Enable/disable specific alert types per city

## Technical Stack

- **Laravel**: PHP framework for backend
- **Livewire**: For interactive UI components
- **Jetstream**: User authentication and profile management
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
5. Seed the database with sample cities (optional)
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```

6. Visit http://localhost in your browser

## Usage

### Anonymous Users
- Visit the homepage to see the service overview and register/login options

### Authenticated Users
1. **Register/Login**: Create an account or login to access your dashboard
2. **Add Cities**: Navigate to "Manage Cities" to subscribe to alerts for cities of interest
3. **Set Custom Thresholds**: Configure precipitation and UV index thresholds for each city
4. **View Dashboard**: See current weather conditions for all your subscribed cities
5. **Receive Alerts**: Get email notifications when conditions in your cities exceed your thresholds

## Testing

Run the test suite with:
```bash
./vendor/bin/sail artisan test
```

The tests cover:
- Weather data retrieval from API
- Alert threshold evaluation and customization
- Email notification sending
- User authentication and city management
- Livewire component functionality

## Scheduled Tasks

The system automatically checks weather conditions hourly. To run the scheduler:

```bash
./vendor/bin/sail artisan schedule:work
```
