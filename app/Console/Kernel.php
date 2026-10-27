<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function bootstrappers()
    {
        $bootstrappers = [];
        foreach (parent::bootstrappers() as $bootstrapper) {
            if ($bootstrapper === \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class) {
                $bootstrappers[] = \App\Bootstrap\LoadIsolatedEnvironment::class;
                continue;
            }

            $bootstrappers[] = $bootstrapper;

            if ($bootstrapper === \Illuminate\Foundation\Bootstrap\LoadConfiguration::class) {
                $bootstrappers[] = \App\Bootstrap\PinApplicationConfig::class;
            }
        }

        return $bootstrappers;
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
