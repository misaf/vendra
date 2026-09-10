<?php

declare(strict_types=1);

use App\Settings\SettingsScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('settings.repositories.tenant.table') ?? 'settings', function (Blueprint $table): void {
            $table->id();

            /*
             | Null for a platform-wide setting, a store for a tenant-scoped
             | one. Nullable so the platform genuinely has no tenant rather
             | than a sentinel id standing in for one.
             */
            TenantSchema::addTenantColumn($table, nullable: true);

            /*
             | The non-null projection of `tenant_id` the uniqueness rests on:
             | `global`, or `tenant:{id}`. A unique key over the nullable column
             | would not hold for platform rows, because MySQL counts every NULL
             | in a unique index as distinct — repeated saves would keep
             | inserting a second global row instead of replacing the first.
             */
            $table->string('scope')->default(SettingsScope::PLATFORM);

            $table->string('group');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->json('payload');

            $table->timestamps();

            $table->unique(['scope', 'group', 'name']);
            TenantSchema::addTenantIndex($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('settings.repositories.tenant.table') ?? 'settings');
    }
};
