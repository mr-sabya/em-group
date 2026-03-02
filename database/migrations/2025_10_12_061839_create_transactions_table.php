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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('payment_method'); // e.g., 'bkash', 'sslcommerz', 'cod'

            // 2. Scoped uniqueness for transaction IDs
            $table->string('transaction_id')->nullable();

            $table->decimal('amount', 15, 2); // Increased precision to match Orders
            $table->string('currency', 3)->default('BDT');
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded', 'partial_refund'])->default('pending');
            $table->enum('type', ['payment', 'refund'])->default('payment');
            $table->json('gateway_response')->nullable();
            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            $table->unique(['tenant_id', 'transaction_id']);

            // Index for faster store-wise financial lookups
            $table->index(['tenant_id', 'status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
