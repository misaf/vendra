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
        Schema::dropIfExists('transactions');
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
        Schema::enableForeignKeyConstraints();
    }
};
