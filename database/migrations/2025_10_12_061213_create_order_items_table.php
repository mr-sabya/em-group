<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('restrict');

            // The "Option" field from your table repeater
            $table->string('option')->nullable();

            // Financials per item
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2);             // Unit Price
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);            // (Price * Qty) - Discount

            $table->timestamps();

            $table->index(['tenant_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
