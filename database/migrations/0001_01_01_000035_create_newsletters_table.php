<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * @return void
     */
    public function up(): void
    {
        $this->createNewslettersTable();
        $this->createNewsletterSubscribersTable();
        $this->createNewsletterSubscriptionTable();
        $this->createNewsletterPostsTable();
        $this->createNewsletterSendHistoriesTable();
        $this->createNewsletterSendHistoryPostTable();
        $this->createNewsletterSendHistorySubscribersTable();
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_send_history_subscribers');
        Schema::dropIfExists('newsletter_send_history_post');
        Schema::dropIfExists('newsletter_send_histories');
        Schema::dropIfExists('newsletter_posts');
        Schema::dropIfExists('newsletter_subscription');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('newsletters');
    }

    /**
     * @return void
     */
    private function createNewslettersTable(): void
    {
        Schema::create('newsletters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->timestampTz('scheduled_at')
                ->nullable();
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'scheduled_at']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * @return void
     */
    private function createNewsletterSubscribersTable(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')
                ->nullable();
            $table->string('email');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique('email');

            $table->index('user_id');
        });
    }

    /**
     * @return void
     */
    private function createNewsletterSubscriptionTable(): void
    {
        Schema::create('newsletter_subscription', function (Blueprint $table): void {
            $table->unsignedBigInteger('newsletter_id');
            $table->unsignedBigInteger('newsletter_subscriber_id');
            $table->timestampTz('subscribed_at')
                ->useCurrent();
            $table->timestampTz('unsubscribed_at')
                ->nullable();

            $table->primary(['newsletter_id', 'newsletter_subscriber_id']);

            $table->index(['newsletter_id', 'subscribed_at'], 'newsletter_subscription_newsletter_subscribed_idx');
            $table->index(['newsletter_id', 'unsubscribed_at'], 'newsletter_subscription_newsletter_unsubscribed_idx');
            $table->index(['newsletter_subscriber_id', 'subscribed_at'], 'newsletter_subscription_subscriber_subscribed_idx');
            $table->index(['newsletter_subscriber_id', 'unsubscribed_at'], 'newsletter_subscription_subscriber_unsubscribed_idx');
        });
    }

    /**
     * @return void
     */
    private function createNewsletterPostsTable(): void
    {
        Schema::create('newsletter_posts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('newsletter_id');
            $table->json('name');
            $table->json('description');
            $table->json('slug');
            $table->string('status');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('newsletter_id');
            $table->index('status');
        });
    }

    /**
     * @return void
     */
    private function createNewsletterSendHistoriesTable(): void
    {
        Schema::create('newsletter_send_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('newsletter_id');
            $table->uuid('token');
            $table->string('status');
            $table->unsignedInteger('total_subscribers');
            $table->unsignedInteger('sent_count')
                ->default(0);
            $table->unsignedInteger('failed_count')
                ->default(0);
            $table->timestampTz('started_at')
                ->useCurrent();
            $table->timestampTz('completed_at')
                ->nullable();

            $table->unique('token');

            $table->index('newsletter_id');
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * @return void
     */
    private function createNewsletterSendHistoryPostTable(): void
    {
        Schema::create('newsletter_send_history_post', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('newsletter_send_history_id');
            $table->unsignedBigInteger('newsletter_post_id');

            $table->unique(['newsletter_send_history_id', 'newsletter_post_id'], 'newsletter_send_history_post_unique_idx');

            $table->index('newsletter_send_history_id');
            $table->index('newsletter_post_id');
        });
    }

    /**
     * @return void
     */
    private function createNewsletterSendHistorySubscribersTable(): void
    {
        Schema::create('newsletter_send_history_subscribers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('newsletter_send_history_id');
            $table->unsignedBigInteger('newsletter_subscriber_id');
            $table->string('status');
            $table->timestampTz('sent_at')
                ->nullable();
            $table->timestampTz('failed_at')
                ->nullable();
            $table->text('failed_message')
                ->nullable();
            $table->timestampsTz();

            $table->unique(['newsletter_send_history_id', 'newsletter_subscriber_id'], 'newsletter_send_history_subscribers_unique_idx');

            $table->index('newsletter_send_history_id', 'newsletter_send_history_subscribers_history_idx');
            $table->index('newsletter_subscriber_id', 'newsletter_send_history_subscribers_subscriber_idx');
            $table->index('status', 'newsletter_send_history_subscribers_status_idx');
        });
    }
};
