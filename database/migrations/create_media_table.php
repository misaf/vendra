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
        // Disable foreign key constraints to drop the 'media' table
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('media');

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
        // Disable foreign key constraints to create the 'media' table
        Schema::disableForeignKeyConstraints();

        Schema::create('media', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->morphs('model');
            $table->uuid('uuid')
                ->nullable()
                ->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')
                ->nullable();
            $table->string('disk');
            $table->string('conversions_disk')
                ->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')
                ->nullable()
                ->index();
            $table->nullableTimestamps();
        });

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
