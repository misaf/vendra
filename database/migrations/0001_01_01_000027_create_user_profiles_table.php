<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            $this->createUserProfilesTable();
        });
    }

    private function createUserProfilesTable(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('description')
                ->nullable();
            $table->string('slug');
            $table->boolean('is_default')
                ->default(false);
            $table->boolean('active')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('active_name_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN name ELSE NULL END');
            $table->string('active_slug_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN slug ELSE NULL END');
            $table->unsignedBigInteger('default_user_guard')
                ->nullable()
                ->virtualAs('CASE WHEN is_default THEN user_id ELSE NULL END');

            $table->index(TenantSchema::tenantIndex(['user_id']));
            $table->unique(TenantSchema::tenantIndex(['active_name_guard']), 'user_profiles_active_name_unique');
            $table->unique(TenantSchema::tenantIndex(['active_slug_guard']), 'user_profiles_active_slug_unique');
            $table->unique(TenantSchema::tenantIndex(['default_user_guard']), 'user_profiles_one_default_per_user_unique');
            $table->index(TenantSchema::tenantIndex(['name']));
            $table->index(TenantSchema::tenantIndex(['slug']));
            $table->index(TenantSchema::tenantIndex(['is_default']));
            $table->index(TenantSchema::tenantIndex(['active']));
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('user_profiles');
        });
    }
};
