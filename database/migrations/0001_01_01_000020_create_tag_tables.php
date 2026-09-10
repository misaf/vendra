<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->json('name');
            $table->json('slug');
            $table->string('type')->nullable();
            $table->integer('position')->nullable();

            $table->timestamps();

            $table->index(TenantSchema::tenantIndex(['position']));
        });

        Schema::create('taggables', function (Blueprint $table): void {
            $table->foreignId('tag_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->morphs('taggable');

            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }
};
