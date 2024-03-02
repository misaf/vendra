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
        // Drop the activity log table using the configured database connection and table name
        Schema::connection(config('activitylog.database_connection'))
            ->dropIfExists(config('activitylog.table_name'));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Create the activity log table using the configured database connection and table name
        Schema::connection(config('activitylog.database_connection'))
            ->create(config('activitylog.table_name'), function (Blueprint $table): void {
                // Define columns
                $table->bigIncrements('id');
                $table->string('log_name')
                    ->nullable();
                $table->text('description');
                $table->nullableMorphs('subject', 'subject');
                $table->nullableMorphs('causer', 'causer');
                $table->json('properties')
                    ->nullable();
                $table->timestamps();

                // Create an index on the log_name column
                $table->index('log_name');
            });
    }
};
