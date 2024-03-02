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
        Schema::dropIfExists('languages');
        Schema::dropIfExists('language_lines');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->char('iso_code')
                ->index();
            $table->boolean('is_default')
                ->index();
            $table->unsignedInteger('position')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('language_lines', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->string('group')
                ->index();
            $table->string('key');
            $table->json('text');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }
};
