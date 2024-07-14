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
        $this->dropUserTables();
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
        $this->createUserTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create user profile Balances table.
     *
     * @return void
     */
    private function createUserProfileBalancesTable(): void
    {
        Schema::create('user_profile_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
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
    }

    /**
     * Create user profile Documents table.
     *
     * @return void
     */
    private function createUserProfileDocumentsTable(): void
    {
        Schema::create('user_profile_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
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
    }

    /**
     * Create user profile Phones table.
     *
     * @return void
     */
    private function createUserProfilePhonesTable(): void
    {
        Schema::create('user_profile_phones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
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
    }

    /**
     * Create user profiles table.
     *
     * @return void
     */
    private function createUserProfilesTable(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
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
    }

    /**
     * Create users table.
     *
     * @return void
     */
    private function createUsersTable(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create user tables.
     *
     * @return void
     */
    private function createUserTables(): void
    {
        $this->createUserProfilePhonesTable();
        $this->createUserProfileDocumentsTable();
        $this->createUserProfileBalancesTable();
        $this->createUserProfilesTable();
        $this->createUsersTable();
    }

    /**
     * Drop user tables.
     *
     * @return void
     */
    private function dropUserTables(): void
    {
        Schema::dropIfExists('user_profile_phones');
        Schema::dropIfExists('user_profile_documents');
        Schema::dropIfExists('user_profile_balances');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
    }
};
