<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->dropProductTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createProductTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create product categories table.
     *
     * @return void
     */
    private function createProductCategoriesTable(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->unsignedBigInteger('position')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create product prices table.
     *
     * @return void
     */
    private function createProductPricesTable(): void
    {
        Schema::create('product_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('price')
                ->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create products table.
     *
     * @return void
     */
    private function createProductsTable(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->string('token')
                ->index()
                ->token();
            $table->tinyInteger('quantity')
                ->nullable()
                ->index();
            $table->tinyInteger('stock_threshold')
                ->nullable()
                ->index();
            $table->boolean('in_stock')
                ->index();
            $table->unsignedBigInteger('position')
                ->index();
            $table->boolean('available_soon')
                ->index();
            $table->timestampTz('availability_date')
                ->nullable()
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create product tables.
     *
     * @return void
     */
    private function createProductTables(): void
    {
        $this->createProductCategoriesTable();
        $this->createProductsTable();
        $this->createProductPricesTable();
    }

    /**
     * Drop product tables.
     *
     * @return void
     */
    private function dropProductTables(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('product_categories');
    }
};
