<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class ProcessWeatherAlerts extends Command
{
    protected $signature = 'weather:process-alerts';

    protected $description = 'Process weather alerts and send notifications';

    public function handle(AlertService $alertService): int
    {
        $this->info('Processing weather alerts...');
        $alertService->processAlerts();
        $this->info('Weather alerts processed successfully!');

        return Command::SUCCESS;
    }
}
