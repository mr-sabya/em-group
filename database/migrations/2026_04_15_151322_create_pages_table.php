<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');


            // Core Information
            $table->string('title');
            $table->string('slug'); // Unique constraint moved to composite index below
            $table->string('page_type')->default('landing');
            $table->string('status')->default('draft');

            // SEO Meta Data
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_robots')->nullable();
            $table->string('og_image')->nullable();

            // Page Builder / Content
            $table->longText('content')->nullable();

            // Scheduling
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Multi-tenant Unique Constraint: 
            // Allows 'about-us' for Tenant 1 and 'about-us' for Tenant 2
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
