<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;
use App\Jobs\NotifyReservationDueDate;

class ScheduleController extends Controller
{
    public function checkReservationDueDate(){
        $today = Carbon::now()->format('Y-m-d');

        $bookings = Booking::with(['tourVehicleSeat.tourVehicle.tour'])
        ->where('status', 'active')
        ->whereNotNull('reservationDueDate')
        ->whereBetween('reservationDueDate', [
            Carbon::today(),
            Carbon::today()->addDays(5)
        ])
        ->get();

        foreach($bookings as $booking){
            dispatch(new NotifyReservationDueDate($booking));
        }
    }
}
