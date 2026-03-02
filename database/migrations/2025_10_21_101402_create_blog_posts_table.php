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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->string('title');

            // 2. Remove ->unique() from here and move to composite unique index below
            $table->string('slug');

            $table->string('image_path')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();

            // Category must belong to the same tenant
            $table->foreignId('blog_category_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            // Allows different stores to have the same slug for different posts
            $table->unique(['tenant_id', 'slug']);

            // Index for faster store-wise lookups
            $table->index(['tenant_id', 'is_published', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
