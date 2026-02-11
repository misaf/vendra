<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('settings');
        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('group');
            $table->string('name');
            $table->boolean('locked')
                ->default(false);
            $table->json('payload');
            $table->timestampsTz();

            $table->unique(['tenant_id', 'group', 'name']);
        });

        Schema::enableForeignKeyConstraints();
    }
};
