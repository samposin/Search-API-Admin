<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
        \App\Console\Commands\VisionApi::class,
        \App\Console\Commands\CurrencyLayerExchangeRateApi::class,
        \App\Console\Commands\SearchClickReport::class,
        \App\Console\Commands\HighVolumeWebsiteReport::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('inspire')->hourly();

        $schedule->command('search_click_report:init')->cron('5 * * * *')->withoutOverlapping();

        $schedule->command('high_volume_website_report:init')->dailyAt('07:05');
    }
}
