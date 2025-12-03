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
        Schema::create('tour_vehicle_seats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tourVehicleId');
            $table->foreign('tourVehicleId')->references('id')->on('tour_vehicles')->onDelete('cascade');
            $table->integer('seatNumber')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_vehicle_seats');
    }
};
