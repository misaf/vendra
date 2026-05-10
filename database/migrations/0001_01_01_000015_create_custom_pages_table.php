<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createCustomPageCategoriesTable();
        $this->createCustomPagesTable();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('custom_pages');
        Schema::dropIfExists('custom_page_categories');
        Schema::enableForeignKeyConstraints();
    }

    private function createCustomPageCategoriesTable(): void
    {
        Schema::create('custom_page_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }

    private function createCustomPagesTable(): void
    {
        Schema::create('custom_pages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('custom_page_category_id');
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'custom_page_category_id']);
            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }
};
