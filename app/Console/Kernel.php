<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\ExpirePendingBookings;
use App\Jobs\CompleteFinishedBookings;
use App\Jobs\SendCheckinReminder;


class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new ExpirePendingBookings)
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->job(new CompleteFinishedBookings)
            ->dailyAt('01:00')
            ->withoutOverlapping();

        $schedule->job(new SendCheckinReminder)
            ->dailyAt('08:00')
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}