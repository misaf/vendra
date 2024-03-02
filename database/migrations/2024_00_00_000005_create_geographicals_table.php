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
        // Disable foreign key constraints during migration rollback
        Schema::disableForeignKeyConstraints();

        // Drop the geographical tables if they exist
        $this->dropGeographicalTables();

        // Re-enable foreign key constraints after migration rollback
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Disable foreign key constraints during migration execution
        Schema::disableForeignKeyConstraints();

        $this->createGeographicalTables();

        // Re-enable foreign key constraints after migration execution
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create the geographical tables.
     *
     * @return void
     */
    private function createGeographicalTables(): void
    {
        Schema::create('geographical_zones', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        Schema::create('geographical_countries', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        Schema::create('geographical_states', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        Schema::create('geographical_cities', function (Blueprint $table): void {
            // Define columns
            $table->id();
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

        Schema::create('geographical_neighborhoods', function (Blueprint $table): void {
            // Define columns
            $table->id();
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
     * Drop the geographical tables.
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
