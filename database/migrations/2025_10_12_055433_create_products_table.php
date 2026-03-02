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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // Basic Info
            // brand_id is now implicitly scoped because the Brand model also has tenant_id
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');

            $table->string('name');

            // 2. Remove ->unique() from slug and sku
            $table->string('slug');
            $table->string('sku')->nullable();

            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->string('thumbnail_image_path')->nullable();
            $table->string('type')->default('normal');

            // Price
            $table->decimal('regular_price', 15, 2)->default(0.00);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('retail_price', 15, 2)->nullable();
            $table->decimal('distributor_price', 15, 2)->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();

            // Product Specifications
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->nullable();
            $table->decimal('volume', 8, 2)->nullable();
            $table->string('volume_unit')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(true);

            // Stock and Order Limits
            $table->boolean('is_manage_stock')->default(false);
            $table->integer('quantity')->default(0);
            $table->integer('min_order_quantity')->default(1);
            $table->integer('max_order_quantity')->nullable();

            // SEO and OG Content
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();

            $table->timestamps();

            // 3. Add Scoped Unique Constraints
            // This prevents duplicate slugs/skus for the SAME tenant, 
            // but allows DIFFERENT tenants to use the same values.
            $table->unique(['tenant_id', 'slug']);
            $table->unique(['tenant_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
