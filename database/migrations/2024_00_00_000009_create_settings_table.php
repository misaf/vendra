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
        Schema::dropIfExists('settings');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->string('group');
            $table->string('name');
            $table->boolean('locked')
                ->default(false);
            $table->json('payload');
            $table->timestamps();

            // Define unique constraint for group and name columns
            $table->unique(['group', 'name']);
        });
    }
};
