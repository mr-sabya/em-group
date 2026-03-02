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
        Schema::create('specification_keys', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->string('name'); // e.g., "Frame"
            $table->string('slug'); // Scoped unique below
            $table->string('group')->nullable(); // e.g., "Dimensions", "Technical"
            $table->timestamps();

            // 2. Multi-tenant Unique Constraints
            $table->unique(['tenant_id', 'slug']);
            // Ensures a tenant doesn't create the same key name in the same group twice
            $table->unique(['tenant_id', 'name', 'group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specification_keys');
    }
};
