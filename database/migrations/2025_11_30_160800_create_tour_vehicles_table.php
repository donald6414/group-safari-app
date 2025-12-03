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
        Schema::create('tour_vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tourId');
            $table->foreign('tourId')->references('id')->on('tours')->onDelete('cascade');
            $table->string('vehicleType')->nullable();
            $table->integer('numberOfVehicle')->nullable();
            $table->integer('numberOfSeat')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_vehicles');
    }
};
