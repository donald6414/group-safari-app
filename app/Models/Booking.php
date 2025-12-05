<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        "clientId",
        "tourVehicleSeatId",
        "startDate",
        "endDate",
        "status",
        "paymentReceipt",
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function tourVehicleSeat()
    {
        return $this->belongsTo(TourVehicleSeat::class, 'tourVehicleSeatId');
    }
}
