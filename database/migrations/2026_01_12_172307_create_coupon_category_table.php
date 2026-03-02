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
        Schema::create('coupon_category', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Relationships
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');

            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            // Prevents linking the same category to the same coupon twice in one store
            $table->unique(['tenant_id', 'coupon_id', 'category_id'], 'coupon_cat_tenant_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_category');
    }
};
