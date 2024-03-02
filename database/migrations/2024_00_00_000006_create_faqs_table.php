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
        // Disable foreign key constraints during migration rollback
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('faqs');

        // Re-enable foreign key constraints after migration rollback
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Disable foreign key constraints during migration execution
        Schema::disableForeignKeyConstraints();

        Schema::create('faq_categories', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('faqs', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->foreignId('faq_category_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->unsignedInteger('position')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Re-enable foreign key constraints after migration execution
        Schema::enableForeignKeyConstraints();
    }
};
