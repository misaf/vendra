<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_images', function (Blueprint $table): void {
            $table->id();
            $table->string('image')->unique();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_images');
    }
};
