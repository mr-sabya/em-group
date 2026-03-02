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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('cascade'); // City might directly belong to a country if no state
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade'); // Redundant but useful if state is null
            $table->string('name');
            $table->timestamps();

            $table->unique(['state_id', 'name']); // Prevent duplicate cities within a state
            $table->unique(['country_id', 'name']); // Fallback if state_id is null, though state_id unique is preferred
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
