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
        Schema::create('inquiries', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('occasion')->nullable();
            $table->text('message');
            $table->string('status');
            $table->string('source')->nullable();
            $table->string('locale', 35)->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('answered_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['status']));
            $table->index(TenantSchema::tenantIndex(['email']));
            $table->index(TenantSchema::tenantIndex(['occasion']));
            $table->index(TenantSchema::tenantIndex(['created_at']));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
