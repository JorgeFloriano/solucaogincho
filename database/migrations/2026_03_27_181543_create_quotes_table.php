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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type');
            $table->string('pickup');
            $table->string('destination');
            $table->decimal('distance_km', 8, 2);
            $table->decimal('price', 8, 2);
            $table->boolean('free_wheels')->default(false);
            $table->boolean('unlocked_gearbox')->default(false);
            $table->boolean('empty_load')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
