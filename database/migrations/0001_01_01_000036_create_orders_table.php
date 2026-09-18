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
            $this->createOrdersTable();
            $this->createOrderLinesTable();
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('order_lines');
            Schema::dropIfExists('orders');
        });
    }

    private function createOrdersTable(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->nullableMorphs('customer');
            $table->foreignId('cart_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('transaction_gateway_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->string('number');
            $table->string('status');
            $table->char('currency_code', 3)
                ->default(Config::string('app.currency'));
            $table->unsignedBigInteger('items_amount')
                ->default(0);
            $table->unsignedBigInteger('delivery_amount')
                ->default(0);
            $table->unsignedBigInteger('total_amount')
                ->default(0);
            $table->string('payment_reference')
                ->nullable();
            $table->text('card_message')
                ->nullable();
            $table->timestampTz('placed_at')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(TenantSchema::tenantIndex(['number']));
            $table->index(TenantSchema::tenantIndex(['cart_id']));
            $table->index(TenantSchema::tenantIndex(['transaction_gateway_id']));
            $table->index(TenantSchema::tenantIndex(['status']));
            $table->index(TenantSchema::tenantIndex(['currency_code']));
            $table->index(TenantSchema::tenantIndex(['placed_at']));
        });
    }

    private function createOrderLinesTable(): void
    {
        Schema::create('order_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('sellable');
            $table->json('name');
            $table->char('currency_code', 3)
                ->default(Config::string('app.currency'));
            $table->unsignedInteger('quantity')
                ->default(1);
            $table->unsignedBigInteger('unit_amount')
                ->default(0);
            $table->unsignedBigInteger('line_amount')
                ->default(0);
            $table->json('metadata')
                ->nullable();
            $table->timestampsTz();

            $table->index('order_id');
        });
    }
};
