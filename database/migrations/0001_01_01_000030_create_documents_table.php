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
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('type');
            $table->char('issuing_country_code', 2)->nullable();
            $table->string('number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestampTz('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['user_profile_id']));
            $table->index(TenantSchema::tenantIndex(['type']));
            $table->index(TenantSchema::tenantIndex(['issuing_country_code']));
            $table->index(TenantSchema::tenantIndex(['number']));
            $table->index(TenantSchema::tenantIndex(['expires_at']));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
