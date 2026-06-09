<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createCurrencyCategoriesTable();
        $this->createCurrenciesTable();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('currency_categories');
        Schema::enableForeignKeyConstraints();
    }

    private function createCurrencyCategoriesTable(): void
    {
        Schema::create('currency_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->string('description')
                ->nullable();
            $table->string('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }

    private function createCurrenciesTable(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('currency_category_id');
            $table->string('name');
            $table->string('description')
                ->nullable();
            $table->string('slug');
            $table->char('iso_code');
            $table->string('conversion_rate');
            $table->string('decimal_place');
            $table->integer('buy_price');
            $table->integer('sell_price');
            $table->boolean('is_default')
                ->default(false);
            $table->unsignedBigInteger('position');
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'currency_category_id']);
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'iso_code']);
            $table->index(['tenant_id', 'is_default']);
            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }
};
