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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            // 1. Multi-tenancy Context
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Parent Product Link
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // 3. Variant Specific Info
            // Removed ->unique() here to move it to a composite index below
            $table->string('sku')->nullable();
            $table->string('thumbnail_image_path')->nullable();

            // 4. Variant-Specific Prices
            // Using 15,2 for financial consistency with the Products table
            $table->decimal('regular_price', 15, 2)->default(0.00);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('retail_price', 15, 2)->nullable();
            $table->decimal('distributor_price', 15, 2)->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();

            // 5. Stock and Order Limits
            $table->boolean('is_manage_stock')->default(false);
            $table->integer('quantity')->default(0);
            $table->integer('min_order_quantity')->default(1);
            $table->integer('max_order_quantity')->nullable();

            // 6. Product Specifications
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->nullable();
            $table->decimal('volume', 8, 2)->nullable();
            $table->string('volume_unit')->nullable();

            // 7. Status & System
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            // 8. Multi-tenant Unique Constraint
            // Prevents duplicate SKUs within the same tenant/store
            $table->unique(['tenant_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
