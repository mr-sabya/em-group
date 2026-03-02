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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Remove ->unique() from here and move to composite index
            $table->string('code');

            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping']);
            $table->decimal('value', 10, 2);

            $table->decimal('min_spend', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();

            $table->integer('usage_limit_per_coupon')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('usage_limit_per_user')->nullable();

            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            // Prevents Tenant A from having two "SAVE10" codes, 
            // but allows Tenant A and Tenant B to both have "SAVE10".
            $table->unique(['tenant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
