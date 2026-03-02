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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Link to original products (Restrict deletion to preserve order history)
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('restrict');

            // 2. Snapshot of product data (Crucial for multi-tenancy history)
            $table->string('item_name');
            $table->string('item_sku')->nullable();
            $table->json('item_attributes')->nullable(); // E.g., {"Color": "Red", "Size": "M"}

            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('item_tax_amount', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2);

            // Removed vendor_id, commission_rate, and commission_amount

            $table->timestamps();

            // Index for performance
            $table->index(['tenant_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
