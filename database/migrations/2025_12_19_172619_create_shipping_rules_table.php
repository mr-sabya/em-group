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
        Schema::create('shipping_rules', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Relationships
            $table->foreignId('shipping_method_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->constrained();
            $table->foreignId('state_id')->nullable()->constrained(); // Null = All states
            $table->foreignId('city_id')->nullable()->constrained();  // Null = All cities

            $table->decimal('cost', 15, 2)->default(0.00);
            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            // This prevents duplicate rules for the same location within a specific store/method
            $table->unique(
                ['tenant_id', 'shipping_method_id', 'country_id', 'state_id', 'city_id'],
                'ship_rule_unique_location'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rules');
    }
};
