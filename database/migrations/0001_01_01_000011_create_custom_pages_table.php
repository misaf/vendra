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
            $this->createCustomPageCategoriesTable();
            $this->createCustomPagesTable();
        });
    }

    private function createCustomPageCategoriesTable(): void
    {
        Schema::create('custom_page_categories', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('active')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
        });
    }

    private function createCustomPagesTable(): void
    {
        Schema::create('custom_pages', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('custom_page_category_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('active')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['custom_page_category_id']));
            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('custom_pages');
            Schema::dropIfExists('custom_page_categories');
        });
    }
};
