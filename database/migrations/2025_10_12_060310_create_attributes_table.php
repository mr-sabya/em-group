<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AttributeDisplayType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enumCases = array_map(fn($case) => $case->value, AttributeDisplayType::cases());

        Schema::create('attributes', function (Blueprint $table) use ($enumCases) {
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

            // Enum for display type
            $table->enum('display_type', $enumCases)
                ->default(AttributeDisplayType::Text->value);

            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // 2. Multi-tenant Unique Constraints
            // Allows different tenants to have an attribute named "Color"
            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
