<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table acts as a bridge (pivot) between Admins and Tenants.
     * It is used to track which Executive Admins have access to which specific stores.
     */
    public function up(): void
    {
        Schema::create('admin_tenant', function (Blueprint $table) {
            // 1. Link to the Admin (using the ID from 'admins' table)
            $table->foreignId('admin_id')
                ->constrained('admins')
                ->onDelete('cascade');

            // 2. Link to the Tenant (using the string ID from 'tenants' table)
            // Note: Stancl Tenancy uses a string (slug) as the primary key for tenants.
            $table->string('tenant_id');

            // 3. Define the Foreign Key relationship for the Tenant
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 4. (Optional but Recommended) Prevent duplicate assignments
            // Ensures an admin cannot be assigned to the same tenant twice.
            $table->primary(['admin_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_tenant');
    }
};
