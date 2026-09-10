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
        Schema::disableForeignKeyConstraints();
        $this->createNewslettersTable();
        $this->createNewsletterSubscribersTable();
        $this->createNewsletterDeliveriesTable();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('newsletter_deliveries');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('newsletters');
        Schema::enableForeignKeyConstraints();
    }

    private function createNewslettersTable(): void
    {
        Schema::create('newsletters', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->string('subject');
            $table->text('content')
                ->nullable();
            $table->string('status')
                ->default('draft');
            $table->timestampTz('scheduled_at')
                ->nullable();
            $table->timestampTz('sent_at')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(TenantSchema::tenantIndex(['status']));
            $table->index(TenantSchema::tenantIndex(['scheduled_at']));
        });
    }

    private function createNewsletterSubscribersTable(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table): void {
            $table->id();
            TenantSchema::addTenantColumn($table);
            $table->string('email');
            $table->string('name')
                ->nullable();
            $table->string('unsubscribe_token');
            $table->timestampTz('subscribed_at')
                ->nullable();
            $table->timestampTz('unsubscribed_at')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(TenantSchema::tenantIndex(['email']));
            $table->unique('unsubscribe_token');
            $table->index(TenantSchema::tenantIndex(['unsubscribed_at']));
        });
    }

    private function createNewsletterDeliveriesTable(): void
    {
        Schema::create('newsletter_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')
                ->constrained('newsletter_subscribers')
                ->cascadeOnDelete();
            $table->timestampTz('sent_at')
                ->nullable();

            $table->unique(
                ['newsletter_id', 'newsletter_subscriber_id'],
                'newsletter_delivery_recipient_unique',
            );
        });
    }
};
