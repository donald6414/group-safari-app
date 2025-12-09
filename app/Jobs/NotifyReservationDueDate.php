<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Notifications\SeatReservationDueDate;

class NotifyReservationDueDate implements ShouldQueue
{
    use Queueable;

    public $bookingData;

    /**
     * Create a new job instance.
     */
    public function __construct($booking)
    {
        $this->bookingData = $booking;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $emailData = [
            'packageTitle' => $this->bookingData->tourVehicleSeat->tourVehicle->tour->title,
            'seatNumber' => $this->bookingData->tourVehicleSeat->seatNumber,
            'clientName' => $this->bookingData->client->name,
            'agentName' => $this->bookingData->client->user->name,
        ];

        $today = Carbon::today();

        $dueDate = $this->bookingData->reservationDueDate;

        // How many days from today to due date (can be negative if overdue)
        $daysRemaining = $today->diffInDays($dueDate, false); // false = allow negative
        Log::info("Days remaining ". $daysRemaining);

        // Boolean - is today exactly the due date?
        $isDueToday = $dueDate->isToday();

        if ($isDueToday) {
            $emailData['isDueDate'] = true;
            $emailData['daysLeft'] = 0;

            $booking = Booking::findOrFail($this->bookingData->id);
            $booking->status = 'cancelled';
            $booking->save();

            $this->bookingData->tourVehicleSeat->status = 'available';
            $this->bookingData->tourVehicleSeat->save();

            $this->bookingData->client->user->notify(new SeatReservationDueDate($emailData));
        }else {
            $emailData['isDueDate'] = false;
            $emailData['daysLeft'] = $daysRemaining;

            $this->bookingData->client->user->notify(new SeatReservationDueDate($emailData));
        }
    }
}
