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
        $this->dropPageTables();
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
        $this->createPageTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create page categories table.
     *
     * @return void
     */
    private function createPageCategoriesTable(): void
    {
        Schema::create('page_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name')
                ->index();
            $table->text('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create pages table.
     *
     * @return void
     */
    private function createPagesTable(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('page_category_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name')
                ->index();
            $table->text('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create page tables.
     *
     * @return void
     */
    private function createPageTables(): void
    {
        $this->createPageCategoriesTable();
        $this->createPagesTable();
    }

    /**
     * Drop page tables.
     *
     * @return void
     */
    private function dropPageTables(): void
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
    }
};
