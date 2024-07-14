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
        $this->dropOrderTables();
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
        $this->createOrderTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create order products table.
     *
     * @return void
     */
    private function createOrderProductsTable(): void
    {
        Schema::create('order_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->tinyInteger('quantity')
                ->index();
            $table->unsignedBigInteger('unit_price')
                ->index();
            $table->unsignedBigInteger('tax_amount')
                ->index();
            $table->unsignedBigInteger('discount_amount')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create orders table.
     *
     * @return void
     */
    private function createOrdersTable(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('description')
                ->nullable();
            $table->unsignedBigInteger('discount_amount')
                ->nullable()
                ->index();
            $table->string('reference_code')
                ->index();
            $table->string('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create order tables.
     *
     * @return void
     */
    private function createOrderTables(): void
    {
        $this->createOrdersTable();
        $this->createOrderProductsTable();
    }

    /**
     * Drop order tables.
     *
     * @return void
     */
    private function dropOrderTables(): void
    {
        Schema::dropIfExists('order_products');
        Schema::dropIfExists('orders');
    }
};
