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
        $this->dropTenantTables();
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
        $this->createTenantTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create tenant domains table.
     *
     * @return void
     */
    private function createTenantDomainsTable(): void
    {
        Schema::create('tenant_domains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name')
                ->index();
            $table->text('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create tenants table.
     *
     * @return void
     */
    private function createTenantsTable(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name')
                ->index();
            $table->text('description')
                ->nullable();
            $table->string('slug')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create tenant tables.
     *
     * @return void
     */
    private function createTenantTables(): void
    {
        $this->createTenantsTable();
        $this->createTenantUsersTable();
        $this->createTenantDomainsTable();
    }

    /**
     * Create tenant users table.
     *
     * @return void
     */
    private function createTenantUsersTable(): void
    {
        Schema::create('tenant_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestampsTz();
        });
    }

    /**
     * Drop tenant tables.
     *
     * @return void
     */
    private function dropTenantTables(): void
    {
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('tenants');
    }
};
