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
        Schema::create('attribute_attribute_set', function (Blueprint $table) {
            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->foreignId('attribute_set_id')->constrained('attribute_sets')->onDelete('cascade');

            // 2. Composite Primary Key including tenant_id
            // We give it a custom name because the default generated name might exceed 64 characters
            $table->primary(['tenant_id', 'attribute_id', 'attribute_set_id'], 'attr_attr_set_tenant_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_attribute_set');
    }
};
