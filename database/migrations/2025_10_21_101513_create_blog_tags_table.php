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
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            // 2. Multi-tenant Unique Constraints
            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'slug']);
        });

        // Pivot table for many-to-many relationship
        Schema::create('blog_post_blog_tag', function (Blueprint $table) {
            // 3. Add Tenant ID to Pivot
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->foreignId('blog_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('blog_tag_id')->constrained()->onDelete('cascade');

            // 4. Primary key including tenant
            $table->primary(['tenant_id', 'blog_post_id', 'blog_tag_id'], 'bp_bt_tenant_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_blog_tag');
        Schema::dropIfExists('blog_tags');
    }
};
