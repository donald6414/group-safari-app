<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('clientId');
            $table->foreign('clientId')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('tourVehicleSeatId');
            $table->foreign('tourVehicleSeatId')->references('id')->on('tour_vehicle_seats')->onDelete('cascade');
            $table->string('startDate')->nullable();
            $table->string('endDate')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
