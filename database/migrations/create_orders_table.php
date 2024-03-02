<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Define a new migration using an anonymous class
return new class () extends Migration {
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('order_products');
        Schema::dropIfExists('orders');

        // Enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();

        Schema::create('orders', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        Schema::create('order_products', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        // Enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
