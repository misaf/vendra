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
        Schema::withoutForeignKeyConstraints(function (): void {
            $this->createWishlistsTable();
            $this->createWishlistItemsTable();
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('wishlist_items');
            Schema::dropIfExists('wishlists');
        });
    }

    private function createWishlistsTable(): void
    {
        Schema::create('wishlists', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->nullableMorphs('owner');
            $table->uuid('token')->unique();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestampsTz();

            $table->index(TenantSchema::tenantIndex(['is_default']));
        });
    }

    private function createWishlistItemsTable(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wishlist_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('sellable');
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->unique(['wishlist_id', 'sellable_type', 'sellable_id']);
        });
    }
};
