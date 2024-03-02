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

        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');

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

        Schema::create('product_categories', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        Schema::create('products', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        Schema::create('product_prices', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        // Enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
