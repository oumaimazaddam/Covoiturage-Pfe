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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('departure'); 
            $table->string('destination'); 
            $table->time('departure_time');
            $table->time('estimate_arrival_time')->nullable();
            $table->decimal('price', 8, 2);
            $table->unsignedBigInteger('driver_id');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('rating', 2, 1)->nullable();
            $table->boolean('instant_booking')->default(false);
            $table->integer('available_seats')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
