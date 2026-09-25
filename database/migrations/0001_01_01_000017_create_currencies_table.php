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
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table, nullable: true);
            $table->string('code', 16);
            $table->string('name');
            $table->string('symbol', 16)->nullable();
            $table->unsignedTinyInteger('decimal_places');
            $table->string('type', 8)->default('fiat');
            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('default_guard')
                ->nullable()
                ->virtualAs(TenantSchema::enabled()
                    ? 'CASE WHEN is_default THEN '.TenantSchema::column().' ELSE NULL END'
                    : 'CASE WHEN is_default THEN 1 ELSE NULL END');
            $table->unsignedBigInteger('position');
            $table->timestampsTz();

            /*
            | Platform currencies (the console's, used for billing) carry a null
            | tenant id, where the tenant-scoped uniques stop discriminating.
            */
            if (TenantSchema::enabled()) {
                $table->string('platform_code_guard', 16)
                    ->nullable()
                    ->virtualAs('CASE WHEN '.TenantSchema::column().' IS NULL THEN code ELSE NULL END');
                $table->unsignedTinyInteger('platform_default_guard')
                    ->nullable()
                    ->virtualAs('CASE WHEN is_default AND '.TenantSchema::column().' IS NULL THEN 1 ELSE NULL END');
            }

            $table->unique(TenantSchema::tenantIndex(['code']));
            $table->unique('default_guard', 'currencies_one_default_unique');
            if (TenantSchema::enabled()) {
                $table->unique('platform_code_guard', 'currencies_platform_code_unique');
                $table->unique('platform_default_guard', 'currencies_platform_one_default_unique');
            }

            $table->index(TenantSchema::tenantIndex(['type']));
            $table->index(TenantSchema::tenantIndex(['active']));
            $table->index(TenantSchema::tenantIndex(['is_default']));
            $table->index(TenantSchema::tenantIndex(['position']));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
