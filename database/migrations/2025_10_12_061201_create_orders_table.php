<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // Relationships
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null'); // Agent
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');   // Customer
            $table->foreignId('courier_id')->nullable()->constrained('couriers')->onDelete('set null');

            // Order Identity
            $table->string('order_number');

            // Customer Information
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('district')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Notes
            $table->text('customer_note')->nullable();
            $table->text('courier_note')->nullable();

            // Source
            $table->string('source')->default('landing_page');

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('delivery_fee', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('coupon_discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('coupon_code')->nullable();

            // Payment Methods
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_method_name')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_phone_number')->nullable();
            $table->string('payment_status')->nullable(); // Cast to PaymentStatus Enum

            // Shipping Methods
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->string('shipping_method_name')->nullable();
            $table->string('tracking_number')->nullable();

            // Order Status
            $table->string('status')->default('pending');

            // Cancellation
            $table->foreignId('cancel_reason_id')->nullable()->constrained('cancel_reasons')->nullOnDelete();
            $table->text('cancel_note')->nullable();
            $table->foreignId('cancelled_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Indices
            $table->unique(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
