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
        \App\Console\Commands\GeoImportCommand::class,
        \App\Console\Commands\QaCheck::class,
        \App\Console\Commands\DemoSaasCommand::class,
        \App\Console\Commands\DemoSaasFullCommand::class,
        \App\Console\Commands\ExportSchedaClienteModule::class,
        \App\Console\Commands\SetupConfigurazioneCommand::class,
        \App\Console\Commands\CleanupComponentiOrfaniCommand::class,
        \App\Console\Commands\RenumberClientiCommand::class,
        \App\Console\Commands\RenumberSchedineCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
