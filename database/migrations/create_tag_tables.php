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
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->string('type')
                ->nullable();
            $table->integer('position')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table): void {
            // Define columns
            $table->foreignId('tag_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('taggable');
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }
};
