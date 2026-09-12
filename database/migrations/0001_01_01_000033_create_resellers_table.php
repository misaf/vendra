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
    }

    public function down(): void
    {
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
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('active_reseller_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN reseller_id ELSE NULL END');

            $table->unique('active_reseller_guard', 'reseller_users_active_reseller_unique');
            $table->unique(['reseller_id', 'user_id'], 'reseller_users_reseller_user_unique');
        });
    }
};
