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
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->string('value'); // e.g., "Red", "16 GB"
            $table->string('slug')->nullable();
            $table->json('metadata')->nullable(); // e.g., {"hex_code": "#FF0000"}
            $table->timestamps();

            // 2. Multi-tenant Unique Constraint
            // Ensures "Red" is unique for the "Color" attribute within a specific store
            $table->unique(['tenant_id', 'attribute_id', 'value']);

            // If you use slugs for filtering in the URL, scope that too
            $table->unique(['tenant_id', 'attribute_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
