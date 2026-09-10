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
        Schema::create('socialite_users', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_id');
            $table->timestampsTz();

            $table->unique([
                'provider',
                'provider_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socialite_users');
    }
};
