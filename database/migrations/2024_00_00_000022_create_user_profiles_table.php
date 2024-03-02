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
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('user_profile_phones');
        Schema::dropIfExists('user_profile_documents');
        Schema::dropIfExists('user_profile_balances');

        // Enable foreign key constraints
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

        Schema::create('user_profiles', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('first_name')
                ->nullable()
                ->index();
            $table->string('last_name')
                ->nullable()
                ->index();
            $table->text('description')
                ->nullable();
            $table->timestampTz('birthdate')
                ->nullable()
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('user_profile_phones', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->foreignId('user_profile_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->char('country')
                ->index();
            $table->string('phone')
                ->index();
            $table->string('phone_normalized')
                ->index();
            $table->string('phone_national')
                ->index();
            $table->string('phone_e164')
                ->index();
            $table->string('status')
                ->index();
            $table->timestampTz('verified_at')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('user_profile_documents', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->foreignId('user_profile_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('status')
                ->index();
            $table->timestampTz('verified_at')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('user_profile_balances', function (Blueprint $table): void {
            // Define columns
            $table->id();
            $table->foreignId('user_profile_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->bigInteger('amount')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::enableForeignKeyConstraints();
    }
};
