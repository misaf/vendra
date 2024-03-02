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
        // Disable foreign key constraints to drop tables
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('tax_categories');
        Schema::dropIfExists('tax_geographical_countries');

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Disable foreign key constraints to create tables
        Schema::disableForeignKeyConstraints();

        Schema::create('tax_categories', function (Blueprint $table): void {
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

        Schema::create('tax_geographical_countries', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->foreignId('tax_category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('geographical_country_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
