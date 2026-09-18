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
        $this->createAttributesTable();
        $this->createAttributeValuesTable();
        $this->createAttributeValueSelectionsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_selections');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }

    private function createAttributesTable(): void
    {
        Schema::create('attributes', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 64)->nullable();
            $table->unsignedBigInteger('position');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('active_name_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN name ELSE NULL END');

            $table->unique(TenantSchema::tenantIndex(['active_name_guard']), 'attributes_active_name_unique');
            $table->index(TenantSchema::tenantIndex(['name']));
            $table->index(TenantSchema::tenantIndex(['unit']));
            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
        });
    }

    private function createAttributeValuesTable(): void
    {
        Schema::create('attribute_values', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('attribute_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('attributable');
            $table->string('value');
            $table->unsignedBigInteger('position');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('active_value_guard')
                ->nullable()
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN value ELSE NULL END');

            $table->unique(
                TenantSchema::tenantIndex(['attribute_id', 'attributable_type', 'attributable_id', 'active_value_guard']),
                'attribute_values_active_value_unique',
            );
            $table->index(TenantSchema::tenantIndex(['attribute_id']));
            $table->index(TenantSchema::tenantIndex(['position']));
        });
    }

    private function createAttributeValueSelectionsTable(): void
    {
        Schema::create('attribute_value_selections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attribute_value_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('selectable');
            $table->timestampsTz();

            $table->unique(
                ['attribute_value_id', 'selectable_type', 'selectable_id'],
                'attribute_value_selections_unique',
            );
        });
    }
};
