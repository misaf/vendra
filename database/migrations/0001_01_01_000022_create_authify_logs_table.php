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
        Schema::create('authify_logs', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_id')->constrained();
            $table->tinyInteger('action');
            $table->ipAddress('ip_address');
            $table->char('ip_country', 2)->default('XX');
            $table->mediumText('user_agent');
            $table->timestampsTz();

            $table->index(TenantSchema::tenantIndex(['user_id']));
            $table->index(TenantSchema::tenantIndex(['action']));
            $table->index(TenantSchema::tenantIndex(['ip_address']));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authify_logs');
    }
};
