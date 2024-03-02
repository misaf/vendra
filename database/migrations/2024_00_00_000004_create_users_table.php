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

        Schema::dropIfExists('users');

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

        Schema::create('users', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // Re-enable foreign key constraints after migration execution
        Schema::enableForeignKeyConstraints();
    }
};
