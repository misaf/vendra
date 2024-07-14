<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->dropLanguageTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createLanguageTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create language lines table.
     *
     * @return void
     */
    private function createLanguageLinesTable(): void
    {
        Schema::create('language_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('group')
                ->index();
            $table->string('key');
            $table->json('text');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create languages table.
     *
     * @return void
     */
    private function createLanguagesTable(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
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
    }

    /**
     * Create language tables.
     *
     * @return void
     */
    private function createLanguageTables(): void
    {
        $this->createLanguagesTable();
        $this->createLanguageLinesTable();
    }

    /**
     * Drop language tables.
     *
     * @return void
     */
    private function dropLanguageTables(): void
    {
        Schema::dropIfExists('languages');
        Schema::dropIfExists('language_lines');
    }
};
