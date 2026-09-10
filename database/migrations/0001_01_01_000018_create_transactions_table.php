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
            $this->createTransactionGatewaysTable();
            $this->createWalletsTable();
            $this->createLedgerEntriesTable();
            $this->createTransactionsTable();
            $this->createTransactionFeesTable();
            $this->createTransactionMetadataTable();
            $this->createTransactionLimitsTable();
        });
    }

    private function createTransactionGatewaysTable(): void
    {
        Schema::create('transaction_gateways', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->string('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('active');
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('default_guard')
                ->nullable()
                ->virtualAs(TenantSchema::enabled()
                    ? 'CASE WHEN is_default THEN '.TenantSchema::column().' ELSE NULL END'
                    : 'CASE WHEN is_default THEN 1 ELSE NULL END');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique('default_guard', 'transaction_gateways_one_default_unique');
            $table->index(TenantSchema::tenantIndex(['slug']));
            $table->index(TenantSchema::tenantIndex(['position']));
            $table->index(TenantSchema::tenantIndex(['active']));
            $table->index(TenantSchema::tenantIndex(['is_default']));
        });
    }

    private function createWalletsTable(): void
    {
        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('currency_code', 16);
            $table->bigInteger('balance')
                ->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(TenantSchema::tenantIndex(['user_id', 'currency_code']));
            $table->index(TenantSchema::tenantIndex(['currency_code']));
        });
    }

    private function createLedgerEntriesTable(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wallet_id')
                ->constrained()
                ->restrictOnDelete();
            $table->nullableMorphs('source');
            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->timestampTz('created_at');

            $table->index(['wallet_id', 'created_at']);
        });
    }

    private function createTransactionsTable(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('wallet_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('transaction_gateway_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('counterparty_wallet_id')
                ->nullable()
                ->constrained('wallets')
                ->restrictOnDelete();
            $table->string('transaction_type');
            $table->string('token');
            $table->string('idempotency_key')
                ->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('status');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['wallet_id']));
            $table->index(TenantSchema::tenantIndex(['transaction_gateway_id']));
            $table->index(TenantSchema::tenantIndex(['transaction_type']));
            $table->index(TenantSchema::tenantIndex(['token']));
            $table->unique(TenantSchema::tenantIndex(['idempotency_key']));
            $table->index(TenantSchema::tenantIndex(['status']));
        });
    }

    private function createTransactionFeesTable(): void
    {
        Schema::create('transaction_fees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestampsTz();

            $table->index(['transaction_id']);
        });
    }

    private function createTransactionMetadataTable(): void
    {
        Schema::create('transaction_metadata', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('key_name');
            $table->string('key_value');
            $table->timestampsTz();

            $table->index(['transaction_id', 'key_name']);
        });
    }

    private function createTransactionLimitsTable(): void
    {
        Schema::create('transaction_limits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('transaction_type');
            $table->unsignedBigInteger('amount');
            $table->timestampsTz();

            $table->unique(['wallet_id', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('transaction_limits');
            Schema::dropIfExists('transaction_metadata');
            Schema::dropIfExists('transaction_fees');
            Schema::dropIfExists('transactions');
            Schema::dropIfExists('ledger_entries');
            Schema::dropIfExists('wallets');
            Schema::dropIfExists('transaction_gateways');
        });
    }
};
