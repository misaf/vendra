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
        Schema::create('verifications', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->string('provider')->nullable();
            $table->string('reference')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['user_profile_id']));
            $table->index(TenantSchema::tenantIndex(['type']));
            $table->index(TenantSchema::tenantIndex(['status']));
            $table->index(TenantSchema::tenantIndex(['reference']));
            $table->index(TenantSchema::tenantIndex(['country_code']));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
