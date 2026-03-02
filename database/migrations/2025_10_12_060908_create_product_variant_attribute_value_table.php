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
        Schema::create('product_variant_attribute_value', function (Blueprint $table) {
            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->onDelete('cascade');

            $table->foreignId('attribute_value_id')
                ->constrained('attribute_values')
                ->onDelete('cascade');

            // 2. Composite Primary Key including tenant_id
            // Custom name to prevent "Identifier too long" errors
            $table->primary(
                ['tenant_id', 'product_variant_id', 'attribute_value_id'],
                'variant_attr_tenant_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_attribute_value');
    }
};
