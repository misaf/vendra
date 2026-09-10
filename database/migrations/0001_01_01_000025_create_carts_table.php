<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->nullableMorphs('owner');
            $table->uuid('token')->unique();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();

            $table->index(TenantSchema::tenantIndex(['expires_at']));
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('sellable');
            $table->unsignedInteger('quantity')->default(1);
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->unique(['cart_id', 'sellable_type', 'sellable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
