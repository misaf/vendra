<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createResellersTable();
        $this->createResellerUsersTable();
        $this->createPasswordResetTokensTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_password_reset_tokens');
        Schema::dropIfExists('reseller_users');
        Schema::dropIfExists('resellers');
    }

    private function createResellersTable(): void
    {
        Schema::create('resellers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')
                ->index();
            $table->text('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('active')
                ->index();
            $table->string('email')
                ->nullable()
                ->index();
            $table->text('offboarding_reason')
                ->nullable();
            $table->timestampTz('offboarded_at')
                ->nullable()
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    private function createResellerUsersTable(): void
    {
        Schema::create('reseller_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reseller_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('username');
            $table->string('email');
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('active_reseller_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN reseller_id ELSE NULL END');
            $table->string('active_username_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN username ELSE NULL END');
            $table->string('active_email_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN email ELSE NULL END');

            $table->unique('active_reseller_guard', 'reseller_users_active_reseller_unique');
            $table->unique('active_username_guard', 'reseller_users_active_username_unique');
            $table->unique('active_email_guard', 'reseller_users_active_email_unique');
        });
    }

    private function createPasswordResetTokensTable(): void
    {
        Schema::create('reseller_password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });
    }
};
