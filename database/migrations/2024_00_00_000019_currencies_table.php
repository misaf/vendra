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

        Schema::dropIfExists('currency_categories');
        Schema::dropIfExists('currencies');

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

        Schema::create('currency_categories', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->string('name');
            $table->string('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('currencies', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->foreignId('currency_category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->char('iso_code')
                ->index();
            $table->string('conversion_rate');
            $table->string('decimal_place');
            $table->boolean('is_default')
                ->index();
            $table->unsignedInteger('position')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
