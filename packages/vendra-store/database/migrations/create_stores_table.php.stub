<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            $this->createStoresTable();
            $this->createStoreUsersTable();
            $this->createStoreDomainsTable();
        });
    }

    private function createStoresTable(): void
    {
        Schema::create('stores', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id')
                ->nullable()
                ->index();
            $table->string('name')
                ->index();
            $table->text('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('active')
                ->index();

            /*
             | The store's own business defaults. Nullable throughout: an
             | unset value means "follow the platform default" rather than a
             | store that was configured wrong.
             */
            $table->string('locale', 16)
                ->nullable();
            $table->string('currency', 3)
                ->nullable();
            $table->string('timezone', 64)
                ->nullable();

            /*
             | Platform-owned annotations about the store — where it came from,
             | what an operator noted. Free-form on purpose; anything the
             | platform queries earns a column instead.
             */
            $table->json('metadata')
                ->nullable();

            $table->timestampTz('billing_suspended_at')
                ->nullable()
                ->index();
            $table->string('provisioning_status')
                ->default('ready')
                ->index();
            $table->boolean('provisioning_should_seed')
                ->default(false);
            $table->timestampTz('provisioning_seeded_at')
                ->nullable();
            $table->timestampTz('routes_cached_at')
                ->nullable();
            $table->timestampTz('provisioned_at')
                ->nullable();
            $table->timestampTz('provisioning_failed_at')
                ->nullable();
            $table->text('provisioning_error')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    private function createStoreUsersTable(): void
    {
        Schema::create('store_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestampsTz();

            $table->unique(['store_id', 'user_id']);
        });
    }

    private function createStoreDomainsTable(): void
    {
        Schema::create('store_domains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name')
                ->index();
            $table->text('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('active')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('active_name_guard')
                ->nullable()
                ->virtualAs('CASE WHEN active = 1 AND deleted_at IS NULL THEN name ELSE NULL END');
            $table->unique('active_name_guard', 'store_domains_active_name_unique');
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('store_domains');
            Schema::dropIfExists('store_user');
            Schema::dropIfExists('stores');
        });
    }
};
