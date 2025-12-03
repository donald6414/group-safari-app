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
        Schema::create('tour_highlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tourId');
            $table->foreign('tourId')->references('id')->on('tours')->onDelete('cascade');
            $table->unsignedBigInteger('highlightId');
            $table->foreign('highlightId')->references('id')->on('highlights')->onDelete('cascade');
            $table->string('startDate')->nullable();
            $table->string('endDate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_highlights');
    }
};
