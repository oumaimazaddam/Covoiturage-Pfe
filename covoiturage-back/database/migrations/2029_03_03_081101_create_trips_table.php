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
            $table->id();  // Colonne auto-incrémentée pour trip_id (clé primaire)
            $table->string('departure');
            $table->string('destination');
            $table->date('trip_date');
            $table->time('departure_time');
            $table->time('estimate_arrival_time')->nullable();
            $table->decimal('price', 8, 2);
            $table->boolean('instant_booking')->default(false);
            $table->integer('available_seats')->default(1);
            $table->timestamps();  // Création des champs created_at et updated_at
        });

        // Créer la table des relations entre passagers et trajets
        Schema::create('trip_passenger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');  // Lier à la table trips
            $table->foreignId('passenger_id')->constrained('users')->onDelete('cascade');  // Lier à la table users (passagers)
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');  // Lier à la table users (conducteurs)
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
