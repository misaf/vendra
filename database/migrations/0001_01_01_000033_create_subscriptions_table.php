<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createPlansTable();
        $this->createSubscriptionsTable();
        $this->createSubscriptionPaymentsTable();
        $this->createSubscriptionInvoicesTables();
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoice_sequences');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }

    private function createPlansTable(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name')
                ->index();
            $table->string('slug')
                ->index();
            $table->text('description')
                ->nullable();
            $table->unsignedInteger('max_units');
            $table->string('period_unit');
            $table->unsignedInteger('period_count');
            $table->unsignedInteger('grace_days')
                ->default(0);
            $table->unsignedBigInteger('price')
                ->default(0);
            $table->char('currency_code', 3)
                ->nullable();
            $table->unsignedInteger('trial_days')
                ->default(0);
            $table->json('features')
                ->nullable();
            $table->json('limits')
                ->nullable();
            $table->boolean('active')
                ->index();
            $table->boolean('is_default')
                ->default(false);
            $table->unsignedBigInteger('default_guard')
                ->nullable()
                ->virtualAs('CASE WHEN is_default AND deleted_at IS NULL THEN 1 ELSE NULL END');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique('default_guard', 'plans_one_default_unique');
            $table->index('is_default');
        });
    }

    private function createSubscriptionsTable(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('subscriber');
            $table->unsignedBigInteger('plan_id')
                ->index();
            $table->string('status')
                ->index();
            $table->unsignedBigInteger('price')
                ->default(0);
            $table->char('currency_code', 3)
                ->nullable();
            $table->timestampTz('trial_ends_at')
                ->nullable();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at')
                ->nullable();
            $table->timestampTz('expiry_reminder_sent_at')
                ->nullable();
            $table->timestampTz('activated_at')
                ->nullable();
            $table->boolean('auto_renews')
                ->default(true);
            $table->unsignedBigInteger('scheduled_plan_id')
                ->nullable()
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('active_subscriber_guard')
                ->nullable()
                ->virtualAs("CASE WHEN status = 'active' AND deleted_at IS NULL THEN subscriber_id ELSE NULL END");

            $table->unique(['subscriber_type', 'active_subscriber_guard'], 'subscriptions_active_subscriber_unique');
        });
    }

    private function createSubscriptionPaymentsTable(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')
                ->constrained()
                ->restrictOnDelete();
            $table->morphs('payer');
            $table->string('provider');
            $table->uuid('idempotency_key')
                ->unique();
            $table->string('provider_reference')
                ->nullable();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('net_amount');
            $table->unsignedBigInteger('tax_amount')
                ->default(0);
            $table->unsignedInteger('tax_rate')
                ->default(0);
            $table->char('currency_code', 3);
            $table->string('status')
                ->default('pending');
            $table->unsignedInteger('attempt_count')
                ->default(0);
            $table->string('failure_code')
                ->nullable();
            $table->text('failure_message')
                ->nullable();
            $table->json('metadata')
                ->nullable();
            $table->timestampTz('processing_at')
                ->nullable();
            $table->timestampTz('paid_at')
                ->nullable();
            $table->timestampTz('failed_at')
                ->nullable();
            $table->timestampTz('next_retry_at')
                ->nullable();
            $table->timestampsTz();

            $table->unique(['provider', 'provider_reference']);
            $table->index(['status', 'next_retry_at']);
        });
    }

    private function createSubscriptionInvoicesTables(): void
    {
        Schema::create('subscription_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_payment_id')
                ->unique()
                ->constrained()
                ->restrictOnDelete();
            $table->morphs('subscriber');
            $table->string('number')
                ->unique();
            $table->timestampTz('issued_at')
                ->index();
            $table->char('currency_code', 3);
            $table->unsignedBigInteger('net_amount');
            $table->unsignedInteger('tax_rate');
            $table->string('tax_label');
            $table->unsignedBigInteger('tax_amount');
            $table->unsignedBigInteger('total_amount');
            $table->json('seller');
            $table->json('buyer');
            $table->json('lines');
            $table->timestampsTz();
        });

        Schema::create('subscription_invoice_sequences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year')
                ->unique();
            $table->unsignedBigInteger('last_number')
                ->default(0);
            $table->timestampsTz();
        });
    }
};
