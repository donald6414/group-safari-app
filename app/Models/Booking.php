<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        "userId",
        "clientId",
        "tourVehicleSeatId",
        "startDate",
        "endDate",
        "status",
        "paymentReceipt",
        "reservationDueDate",
        "reservedAt"
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId', 'id');
    }

    public function tourVehicleSeat()
    {
        return $this->belongsTo(TourVehicleSeat::class, 'tourVehicleSeatId', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }
}
