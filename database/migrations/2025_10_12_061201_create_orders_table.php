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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // Core Relationships
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Removed vendor_id as requested

            // Coupons are store-specific
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');

            // 2. Order number unique per store
            $table->string('order_number');

            // Billing Address Info
            $table->string('billing_first_name');
            $table->string('billing_last_name');
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->foreignId('billing_country_id')->nullable()->constrained('countries');
            $table->foreignId('billing_state_id')->nullable()->constrained('states');
            $table->foreignId('billing_city_id')->nullable()->constrained('cities');
            $table->string('billing_address_line_1');
            $table->string('billing_address_line_2')->nullable();
            $table->string('billing_zip_code');

            // Shipping Address Info
            $table->string('shipping_first_name');
            $table->string('shipping_last_name');
            $table->string('shipping_email')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->foreignId('shipping_country_id')->nullable()->constrained('countries');
            $table->foreignId('shipping_state_id')->nullable()->constrained('states');
            $table->foreignId('shipping_city_id')->nullable()->constrained('cities');
            $table->string('shipping_address_line_1');
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_zip_code');

            // Financials
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('shipping_cost', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('৳');

            // Payment Details
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_method_name')->nullable(); // Snapshot for history
            $table->string('transaction_id')->nullable();
            $table->string('payment_phone_number')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('pending');

            // Shipping Details
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->string('shipping_method_name')->nullable(); // Snapshot for history
            $table->string('tracking_number')->nullable();
            $table->enum('order_status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('pending');

            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            // This allows different tenants to have the same order number sequence (e.g., both have #1001)
            $table->unique(['tenant_id', 'order_number']);

            // Index for faster admin searching
            $table->index(['tenant_id', 'order_status']);
            $table->index(['tenant_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
