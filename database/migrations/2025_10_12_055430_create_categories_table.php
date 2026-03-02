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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // 1. Add the tenant reference
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->string('name');

            // 2. Remove ->unique() from here and move it to a composite index below
            $table->string('slug');

            $table->text('description')->nullable();

            // Self-referencing: Ensure a category belongs to a parent within the same tenant
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');

            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_homepage')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            // 3. Multi-tenant Unique Constraints
            // This allows Tenant A and Tenant B to both have a '/electronics' slug
            $table->unique(['tenant_id', 'slug']);

            // Optional: prevent duplicate category names within the same level for the same tenant
            $table->unique(['tenant_id', 'parent_id', 'name'], 'categories_tenant_parent_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
