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
        $this->dropGeographicalTables();
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
        $this->createGeographicalTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create geographical cities table.
     *
     * @return void
     */
    private function createGeographicalCitiesTable(): void
    {
        Schema::create('geographical_cities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('geographical_state_id')
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
     * Create geographical countries table.
     *
     * @return void
     */
    private function createGeographicalCountriesTable(): void
    {
        Schema::create('geographical_countries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('geographical_zone_id')
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
     * Create geographical neighborhoods table.
     *
     * @return void
     */
    private function createGeographicalNeighborhoodsTable(): void
    {
        Schema::create('geographical_neighborhoods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('geographical_city_id')
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
     * Create geographical states table.
     *
     * @return void
     */
    private function createGeographicalStatesTable(): void
    {
        Schema::create('geographical_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('geographical_country_id')
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
     * Create geographical tables.
     *
     * @return void
     */
    private function createGeographicalTables(): void
    {
        $this->createGeographicalZonesTable();
        $this->createGeographicalCountriesTable();
        $this->createGeographicalStatesTable();
        $this->createGeographicalCitiesTable();
        $this->createGeographicalNeighborhoodsTable();
    }

    /**
     * Create geographical zones table.
     *
     * @return void
     */
    private function createGeographicalZonesTable(): void
    {
        Schema::create('geographical_zones', function (Blueprint $table): void {
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
     * Drop geographical tables.
     *
     * @return void
     */
    private function dropGeographicalTables(): void
    {
        Schema::dropIfExists('geographical_neighborhoods');
        Schema::dropIfExists('geographical_cities');
        Schema::dropIfExists('geographical_states');
        Schema::dropIfExists('geographical_countries');
        Schema::dropIfExists('geographical_zones');
    }
};
