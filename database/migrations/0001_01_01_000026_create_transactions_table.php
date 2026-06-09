<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;

return new class () extends Migration {
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createTransactionGatewaysTable();
        $this->createTransactionsTable();
        $this->createTransactionFeeTable();
        $this->createTransactionTransferTable();
        $this->createTransactionMetadataTable();
        $this->createTransactionChecksTable();
        $this->createTransactionLimitsTable();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('transaction_checks');
        Schema::dropIfExists('transaction_metadata');
        Schema::dropIfExists('transaction_transfers');
        Schema::dropIfExists('transaction_fees');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('transaction_gateways');
        Schema::dropIfExists('transaction_limits');
        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return void
     */
    private function createTransactionGatewaysTable(): void
    {
        Schema::create('transaction_gateways', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('status');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * @return void
     */
    private function createTransactionsTable(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('transaction_gateway_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('transaction_type', [
                TransactionTypeEnum::Deposit->value,
                TransactionTypeEnum::Withdrawal->value,
                TransactionTypeEnum::Commission->value,
                TransactionTypeEnum::Transfer->value,
                TransactionTypeEnum::Bonus->value,
            ]);
            $table->string('token');
            $table->bigInteger('amount');
            $table->string('status');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'transaction_gateway_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'transaction_type']);
            $table->index(['tenant_id', 'token']);
            $table->index(['tenant_id', 'amount']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * @return void
     */
    private function createTransactionFeeTable(): void
    {
        Schema::create('transaction_fees', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->bigInteger('amount');
            $table->timestampsTz();

            $table->index(['transaction_id']);
        });
    }

    /**
     * @return void
     */
    private function createTransactionTransferTable(): void
    {
        Schema::create('transaction_transfers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('user_id');
            $table->timestampsTz();

            $table->index(['transaction_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * @return void
     */
    private function createTransactionMetadataTable(): void
    {
        Schema::create('transaction_metadata', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('key_name');
            $table->string('key_value');
            $table->timestampsTz();

            $table->index(['transaction_id', 'key_name']);
            $table->index(['transaction_id', 'key_value']);
        });
    }

    /**
     * @return void
     */
    private function createTransactionChecksTable(): void
    {
        Schema::create('transaction_checks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->tinyInteger('attempt_count');
            $table->timestampsTz();

            $table->index(['transaction_id', 'attempt_count']);
        });
    }

    /**
     * @return void
     */
    private function createTransactionLimitsTable(): void
    {
        Schema::create('transaction_limits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('transaction_type', [
                TransactionTypeEnum::Deposit->value,
                TransactionTypeEnum::Withdrawal->value,
            ]);
            $table->bigInteger('amount');
            $table->timestampsTz();

            $table->index(['user_id', 'transaction_type']);
        });
    }
};
