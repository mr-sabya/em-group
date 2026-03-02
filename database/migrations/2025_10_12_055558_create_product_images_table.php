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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();

            // 1. Multi-tenancy Context
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Relationships
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // Link to a specific variant (optional)
            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->onDelete('cascade');

            // 3. Image Data
            $table->string('image_path'); // Path to the image file
            $table->boolean('is_thumbnail')->default(false); // Flag for the main product image
            $table->integer('sort_order')->default(0); // For display order in galleries

            $table->timestamps();

            // 4. Indexing
            // Helps quickly load all images for a specific store
            $table->index(['tenant_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
