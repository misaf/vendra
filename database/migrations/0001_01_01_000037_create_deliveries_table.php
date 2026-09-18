<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            $this->createDeliveryZonesTable();
            $this->createDeliverySlotsTable();
            $this->createDeliveriesTable();
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('deliveries');
            Schema::dropIfExists('delivery_slots');
            Schema::dropIfExists('delivery_zones');
        });
    }

    private function createDeliveryZonesTable(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->decimal('origin_latitude', 10, 7);
            $table->decimal('origin_longitude', 10, 7);
            $table->decimal('max_distance_km', 8, 3)
                ->nullable();
            $table->char('currency_code', 3)
                ->default(Config::string('app.currency'));
            $table->unsignedBigInteger('fee_amount')
                ->default(0);
            $table->boolean('requires_quote')
                ->default(false);
            $table->unsignedBigInteger('position');
            $table->boolean('active')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
            $table->index(TenantSchema::tenantIndex(['max_distance_km']));
        });
    }

    private function createDeliverySlotsTable(): void
    {
        Schema::create('delivery_slots', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->json('name');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedInteger('capacity')
                ->nullable();
            $table->unsignedBigInteger('position');
            $table->boolean('active')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
        });
    }

    private function createDeliveriesTable(): void
    {
        Schema::create('deliveries', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('address_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('delivery_zone_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('delivery_slot_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->date('scheduled_for')
                ->nullable();
            $table->decimal('latitude', 10, 7)
                ->nullable();
            $table->decimal('longitude', 10, 7)
                ->nullable();
            $table->decimal('distance_km', 8, 3)
                ->nullable();
            $table->char('currency_code', 3)
                ->default(Config::string('app.currency'));
            $table->unsignedBigInteger('fee_amount')
                ->default(0);
            $table->boolean('requires_quote')
                ->default(false);
            $table->string('recipient_name')
                ->nullable();
            $table->timestampsTz();

            $table->unique(TenantSchema::tenantIndex(['order_id']));
            $table->index(TenantSchema::tenantIndex(['delivery_zone_id']));
            $table->index(TenantSchema::tenantIndex(['delivery_slot_id']));
            $table->index(TenantSchema::tenantIndex(['scheduled_for']));
        });
    }
};
