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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('type')->default('percentage');
            $table->decimal('value', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('banner_image_path')->nullable();
            $table->string('link_target')->nullable();

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('display_order')->default(0);

            $table->timestamps();

            // 2. Multi-tenant Indexing
            $table->index(['tenant_id', 'is_active', 'is_featured']);
        });

        // 3. Updated Pivot Table
        Schema::create('deal_product', function (Blueprint $table) {
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
            $table->foreignId('deal_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Composite primary key including tenant_id
            $table->primary(['tenant_id', 'deal_id', 'product_id'], 'deal_prod_tenant_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_product');
        Schema::dropIfExists('deals');
    }
};
