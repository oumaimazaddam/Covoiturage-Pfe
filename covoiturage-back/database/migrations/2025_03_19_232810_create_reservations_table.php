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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Conducteur
            $table->date('date_trajet');
            $table->time('heure_depart');
            $table->string('ville_depart');
            $table->time('heure_arrivee');
            $table->string('ville_arrivee');
            $table->decimal('prix_total', 8, 2);
            $table->integer('nombre_passagers')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
