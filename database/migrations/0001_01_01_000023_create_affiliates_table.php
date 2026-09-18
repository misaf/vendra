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
            $this->createAffiliatesTable();
            $this->createAffiliateClicksTable();
            $this->createAffiliateReferralsTable();
            $this->createAffiliatePayoutsTable();
            $this->createAffiliateCommissionsTable();
        });
    }

    private function createAffiliatesTable(): void
    {
        Schema::create('affiliates', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('code', 16);
            $table->unsignedTinyInteger('commission_percent')
                ->default(0);
            $table->unsignedBigInteger('signup_bounty')
                ->nullable();
            $table->string('status')
                ->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(TenantSchema::tenantIndex(['code']));
            $table->unique(TenantSchema::tenantIndex(['user_id']));
            $table->index(TenantSchema::tenantIndex(['status']));
        });
    }

    private function createAffiliateClicksTable(): void
    {
        Schema::create('affiliate_clicks', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('affiliate_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('ip_address', 45)
                ->nullable();
            $table->string('user_agent')
                ->nullable();
            $table->string('referer')
                ->nullable();
            $table->string('landing_url')
                ->nullable();
            $table->timestampTz('created_at')
                ->nullable();

            $table->index(TenantSchema::tenantIndex(['affiliate_id', 'created_at']));
        });
    }

    private function createAffiliateReferralsTable(): void
    {
        Schema::create('affiliate_referrals', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('affiliate_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('affiliate_click_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestampTz('attributed_at');
            $table->timestampsTz();

            $table->unique(TenantSchema::tenantIndex(['user_id']));
            $table->index(TenantSchema::tenantIndex(['affiliate_id']));
        });
    }

    private function createAffiliatePayoutsTable(): void
    {
        Schema::create('affiliate_payouts', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('affiliate_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->bigInteger('amount')
                ->default(0);
            $table->string('status')
                ->default('pending');
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestampTz('processed_at')
                ->nullable();
            $table->timestampsTz();

            $table->index(TenantSchema::tenantIndex(['affiliate_id', 'status']));
        });
    }

    private function createAffiliateCommissionsTable(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->foreignId('affiliate_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('affiliate_referral_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('conversion_type');
            $table->string('source_type')
                ->nullable();
            $table->unsignedBigInteger('source_id')
                ->nullable();
            $table->bigInteger('amount')
                ->default(0);
            $table->string('status')
                ->default('pending');
            $table->foreignId('affiliate_payout_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                TenantSchema::tenantIndex(['conversion_type', 'source_type', 'source_id']),
                'affiliate_commissions_conversion_source_unique',
            );
            $table->index(TenantSchema::tenantIndex(['affiliate_id', 'status']));
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('affiliate_commissions');
            Schema::dropIfExists('affiliate_payouts');
            Schema::dropIfExists('affiliate_referrals');
            Schema::dropIfExists('affiliate_clicks');
            Schema::dropIfExists('affiliates');
        });
    }
};
