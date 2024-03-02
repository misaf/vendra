<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Define a new migration using an anonymous class
return new class () extends Migration {
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key constraints for dropping tables
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('transactions');

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key constraints for creating tables
        Schema::disableForeignKeyConstraints();

        Schema::create('transactions', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->morphs('model');
            $table->string('reference_code')
                ->index();
            $table->unsignedBigInteger('amount')
                ->index();
            $table->string('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
