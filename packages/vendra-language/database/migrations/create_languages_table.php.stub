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
            $this->createLanguagesTable();
            $this->createLanguageLinesTable();
        });
    }

    private function createLanguagesTable(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->string('locale', 8);
            $table->boolean('active')->default(true);
            $table->boolean('is_default')
                ->default(false);
            $table->unsignedBigInteger('default_guard')
                ->nullable()
                ->virtualAs(TenantSchema::enabled()
                    ? 'CASE WHEN is_default THEN '.TenantSchema::column().' ELSE NULL END'
                    : 'CASE WHEN is_default THEN 1 ELSE NULL END');
            $table->unsignedBigInteger('position');
            $table->timestampsTz();

            $table->unique(TenantSchema::tenantIndex(['locale']));
            $table->unique('default_guard', 'languages_one_default_unique');
            $table->index(TenantSchema::tenantIndex(['active']));
            $table->index(TenantSchema::tenantIndex(['is_default']));
            $table->index(TenantSchema::tenantIndex(['position']));
        });
    }

    private function createLanguageLinesTable(): void
    {
        Schema::create('language_lines', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->string('namespace')->nullable();
            $table->string('namespace_guard')
                ->virtualAs("COALESCE(namespace, '')");
            $table->string('group')->index();
            $table->string('key');
            $table->json('text');
            $table->timestamps();

            TenantSchema::addTenantIndex($table);
            $table->unique(
                TenantSchema::tenantIndex(['namespace_guard', 'group', 'key']),
                'language_lines_tenant_namespace_group_key_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('language_lines');
            Schema::dropIfExists('languages');
        });
    }
};
