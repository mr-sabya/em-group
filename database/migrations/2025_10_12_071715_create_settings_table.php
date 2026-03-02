<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SettingType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enumCases = array_map(fn($case) => $case->value, SettingType::cases());

        Schema::create('settings', function (Blueprint $table) use ($enumCases) {
            $table->id();
            
            // 1. Add Tenant ID
            $table->string('tenant_id');

            // 2. Create the foreign key constraint manually
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // 2. Remove ->unique() from key and move it to the composite index below
            $table->string('key'); 
            
            $table->string('label')->nullable();
            $table->longText('value')->nullable();
            
            $table->enum('type', $enumCases)->default('string');
            
            $table->text('description')->nullable();
            $table->string('group')->nullable()->index();
            $table->boolean('is_private')->default(false);
            $table->timestamps();

            // 3. Multi-tenant Unique Constraint
            // This allows both Store A and Store B to have their own 'site_name' key.
            $table->unique(['tenant_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};