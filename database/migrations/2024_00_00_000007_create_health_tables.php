<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Health\ResultStores\EloquentHealthResultStore;

// Define a new migration using an anonymous class
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Get the table name for health check history items
        $tableName = EloquentHealthResultStore::getHistoryItemInstance()->getTable();

        Schema::create($tableName, function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->string('check_name');
            $table->string('check_label');
            $table->string('status');
            $table->text('notification_message')
                ->nullable();
            $table->string('short_summary')
                ->nullable();
            $table->json('meta');
            $table->timestamp('ended_at');
            $table->uuid('batch')
                ->index();
            $table->timestampsTz();
        });
    }
};
