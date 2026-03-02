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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Identification (Guest or Logged In)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable()->index();

            // 3. Product Relationships
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // For Bundles/Combos
            $table->foreignId('main_product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->boolean('is_combo')->default(false);

            // 4. Item Data
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2); // Snapshot of price when added to cart
            $table->json('options')->nullable(); // e.g., {"color": "Blue", "size": "XL"}

            $table->timestamps();

            // 5. Multi-tenant Indexing
            // Helps quickly find a specific user's cart within a specific store
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
