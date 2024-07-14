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
        $this->dropCurrencyTables();
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
        $this->createCurrencyTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create currencies table.
     *
     * @return void
     */
    private function createCurrenciesTable(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('currency_category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->char('iso_code')
                ->index();
            $table->string('conversion_rate');
            $table->string('decimal_place');
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
     * Create currency categories table.
     *
     * @return void
     */
    private function createCurrencyCategoriesTable(): void
    {
        Schema::create('currency_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->string('description')
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
     * Create currency tables.
     *
     * @return void
     */
    private function createCurrencyTables(): void
    {
        $this->createCurrencyCategoriesTable();
        $this->createCurrenciesTable();
    }

    /**
     * Drop currency tables.
     *
     * @return void
     */
    private function dropCurrencyTables(): void
    {
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('currency_categories');
    }
};
