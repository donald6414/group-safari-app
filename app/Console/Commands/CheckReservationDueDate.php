<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ScheduleController;

class CheckReservationDueDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-reservation-due-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull all the active bookings and check if the reservations due date is near and notify them and if the due date is to day then cancel the booking automatic and release the seat.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scheduleController = new ScheduleController();
        $scheduleController->checkReservationDueDate();
        $this->info('Reservation due date checked successfully');
    }
}
