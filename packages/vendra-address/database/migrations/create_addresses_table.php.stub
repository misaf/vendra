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
        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('type')->default('other');
            $table->string('label')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('organization')->nullable();
            $table->string('line_one');
            $table->string('line_two')->nullable();
            $table->string('line_three')->nullable();
            $table->string('locality')->nullable();
            $table->string('administrative_area')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('sorting_code')->nullable();
            $table->char('country_code', 2);
            $table->string('locale', 35)->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['user_profile_id']));
            $table->index(TenantSchema::tenantIndex(['country_code']));
            $table->index(TenantSchema::tenantIndex(['locality']));
            $table->index(TenantSchema::tenantIndex(['is_primary']));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
