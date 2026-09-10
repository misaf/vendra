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
            $this->createFaqCategoriesTable();
            $this->createFaqsTable();
        });
    }

    private function createFaqCategoriesTable(): void
    {
        Schema::create('faq_categories', function (Blueprint $table): void {
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

    private function createFaqsTable(): void
    {
        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('faq_category_id')
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

            $table->index(TenantSchema::tenantIndex(['faq_category_id']));
            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('faqs');
            Schema::dropIfExists('faq_categories');
        });
    }
};
