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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Link to User
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('zip_code');
            
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade'); // Foreign key to countries table
            $table->foreignId('state_id')->constrained('states')->onDelete('cascade'); // Foreign key to states table
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade'); // Foreign key to cities table
            
            $table->string('phone')->nullable();
            $table->string('email')->nullable(); // Contact email for this address if different
            $table->enum('type', ['shipping', 'billing', 'both'])->default('shipping'); // Or can be just for labeling
            $table->boolean('is_default')->default(false); // For user's preferred address
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
