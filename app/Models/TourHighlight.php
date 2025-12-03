<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourHighlight extends Model
{
    protected $fillable = [
        "tourId",
        "highlightId",
        "startDate",
        "endDate",
    ];
}
