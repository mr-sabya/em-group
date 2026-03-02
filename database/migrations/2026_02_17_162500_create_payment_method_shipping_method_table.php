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
        Schema::create('payment_method_shipping_method', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Relationships
            $table->foreignId('shipping_method_id')
                ->constrained('shipping_methods')
                ->onDelete('cascade');

            $table->foreignId('payment_method_id')
                ->constrained('payment_methods')
                ->onDelete('cascade');

            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            // Prevents duplicate links within the same store context
            $table->unique(
                ['tenant_id', 'shipping_method_id', 'payment_method_id'],
                'pay_ship_tenant_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_shipping_method');
    }
};
