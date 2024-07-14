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
        $this->dropTransactionTables();
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
        $this->createTransactionTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create transactions table.
     *
     * @return void
     */
    private function createTransactionsTable(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('model');
            $table->string('reference_code')
                ->index();
            $table->unsignedBigInteger('amount')
                ->index();
            $table->string('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create transaction tables.
     *
     * @return void
     */
    private function createTransactionTables(): void
    {
        $this->createTransactionsTable();
    }

    /**
     * Drop transaction tables.
     *
     * @return void
     */
    private function dropTransactionTables(): void
    {
        Schema::dropIfExists('transactions');
    }
};
