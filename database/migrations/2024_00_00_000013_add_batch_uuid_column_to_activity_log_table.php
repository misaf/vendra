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
        // Drop the 'batch_uuid' column from the activity log table using the configured database connection and table name
        Schema::connection(config('activitylog.database_connection'))
            ->table(config('activitylog.table_name'), function (Blueprint $table): void {
                $table->dropColumn('batch_uuid');
            });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Add the 'batch_uuid' column to the activity log table using the configured database connection and table name
        Schema::connection(config('activitylog.database_connection'))
            ->table(config('activitylog.table_name'), function (Blueprint $table): void {
                // Add the 'batch_uuid' column after the 'properties' column
                $table->uuid('batch_uuid')
                    ->nullable()
                    ->after('properties');
            });
    }
};
