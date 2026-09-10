<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;

it('contains every package table in the fresh database baseline', function (): void {
    expect(Schema::hasTable('authify_logs'))->toBeTrue()
        ->and(Schema::hasColumns('language_lines', ['tenant_id', 'namespace', 'namespace_guard']))->toBeTrue()
        ->and(Schema::hasColumns('activity_log', ['tenant_id', 'event', 'attribute_changes']))->toBeTrue()
        ->and(Schema::hasColumns('roles', ['tenant_id', 'description']))->toBeTrue()
        ->and(Schema::hasColumns('permissions', ['tenant_id', 'description']))->toBeTrue()
        ->and(Schema::hasColumn('tags', 'position'))->toBeTrue()
        ->and(Schema::hasColumn('tags', 'order_column'))->toBeFalse()
        ->and(Schema::hasColumns('console_users', ['username', 'email', 'email_verified_at', 'password']))->toBeTrue()
        ->and(Schema::hasColumns('console_password_reset_tokens', ['email', 'token', 'created_at']))->toBeTrue()
        ->and(Schema::hasColumns('reseller_users', ['reseller_id', 'username', 'email', 'email_verified_at', 'password']))->toBeTrue()
        ->and(Schema::hasColumns('reseller_password_reset_tokens', ['email', 'token', 'created_at']))->toBeTrue()
        ->and(Schema::hasColumns('resellers', ['name', 'email', 'offboarding_reason', 'offboarded_at']))->toBeTrue()
        ->and(Schema::hasColumn('resellers', 'owner_name'))->toBeFalse()
        ->and(Schema::hasColumn('resellers', 'owner_email'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'is_console_admin'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'reseller_id'))->toBeFalse();
});

it('names boolean availability columns active', function (string $table): void {
    expect(Schema::hasColumn($table, 'active'))->toBeTrue()
        ->and(Schema::hasColumn($table, 'status'))->toBeFalse();
})->with([
    'attributes',
    'blog_post_categories',
    'blog_posts',
    'currencies',
    'custom_page_categories',
    'custom_pages',
    'faq_categories',
    'faqs',
    'languages',
    'plans',
    'product_categories',
    'resellers',
    'store_domains',
    'stores',
    'transaction_gateways',
    'user_profiles',
]);

it('enforces required relational integrity', function (string $table, string $column): void {
    expect(Schema::hasForeignKey($table, [$column]))->toBeTrue();
})->with([
    ['store_domains', 'store_id'],
    ['store_user', 'store_id'],
    ['store_user', 'user_id'],
    ['blog_posts', 'blog_post_category_id'],
    ['custom_pages', 'custom_page_category_id'],
    ['products', 'product_category_id'],
    ['product_prices', 'product_id'],
    ['faqs', 'faq_category_id'],
    ['transactions', 'transaction_gateway_id'],
    ['transactions', 'wallet_id'],
    ['transactions', 'counterparty_wallet_id'],
    ['wallets', 'user_id'],
    ['ledger_entries', 'wallet_id'],
    ['transaction_fees', 'transaction_id'],
    ['transaction_metadata', 'transaction_id'],
    ['transaction_limits', 'wallet_id'],
    ['attribute_values', 'attribute_id'],
    ['affiliates', 'user_id'],
    ['affiliate_clicks', 'affiliate_id'],
    ['affiliate_referrals', 'affiliate_id'],
    ['affiliate_referrals', 'user_id'],
    ['affiliate_referrals', 'affiliate_click_id'],
    ['affiliate_commissions', 'affiliate_id'],
    ['affiliate_commissions', 'affiliate_referral_id'],
    ['affiliate_commissions', 'affiliate_payout_id'],
    ['affiliate_payouts', 'affiliate_id'],
    ['affiliate_payouts', 'transaction_id'],
    ['socialite_users', 'user_id'],
    ['user_profiles', 'user_id'],
    ['addresses', 'user_profile_id'],
    ['documents', 'user_profile_id'],
    ['phone_numbers', 'user_profile_id'],
    ['verifications', 'user_profile_id'],
    ['authify_logs', 'user_id'],
    ['taggables', 'tag_id'],
    ['model_has_permissions', 'permission_id'],
    ['model_has_roles', 'role_id'],
    ['role_has_permissions', 'permission_id'],
    ['role_has_permissions', 'role_id'],
]);

it('prevents duplicate tenant memberships', function (): void {
    expect(Schema::hasIndex('store_user', ['store_id', 'user_id'], 'unique'))->toBeTrue();
});

it('enforces transaction idempotency keys per tenant', function (): void {
    expect(Schema::hasColumn('transactions', 'idempotency_key'))->toBeTrue()
        ->and(Schema::hasIndex('transactions', ['tenant_id', 'idempotency_key'], 'unique'))->toBeTrue();
});

it('stores durable subscription payment operations with provider identities', function (): void {
    expect(Schema::hasColumns('subscription_payments', [
        'subscription_id',
        'payer_type',
        'payer_id',
        'provider',
        'idempotency_key',
        'provider_reference',
        'amount',
        'currency_code',
        'status',
        'attempt_count',
        'failure_code',
        'failure_message',
        'processing_at',
        'paid_at',
        'failed_at',
        'next_retry_at',
    ]))->toBeTrue()
        ->and(Schema::hasIndex('subscription_payments', ['idempotency_key'], 'unique'))->toBeTrue()
        ->and(Schema::hasIndex('subscription_payments', ['provider', 'provider_reference'], 'unique'))->toBeTrue()
        ->and(Schema::hasForeignKey('subscription_payments', ['subscription_id']))->toBeTrue();
});

it('stores independent tenant availability and durable provisioning state', function (): void {
    expect(Schema::hasColumns('stores', [
        'active',
        'billing_suspended_at',
        'provisioning_status',
        'provisioning_should_seed',
        'provisioning_seeded_at',
        'routes_cached_at',
        'provisioned_at',
        'provisioning_failed_at',
        'provisioning_error',
    ]))->toBeTrue()
        ->and(Schema::hasIndex('stores', ['billing_suspended_at']))->toBeTrue()
        ->and(Schema::hasIndex('stores', ['provisioning_status']))->toBeTrue();
});

it('enforces one active owner and subscription per reseller', function (): void {
    expect(Schema::hasIndex('reseller_users', ['active_reseller_guard'], 'unique'))->toBeTrue()
        ->and(Schema::hasIndex('reseller_users', ['active_username_guard'], 'unique'))->toBeTrue()
        ->and(Schema::hasIndex('reseller_users', ['active_email_guard'], 'unique'))->toBeTrue()
        ->and(Schema::hasForeignKey('reseller_users', ['reseller_id']))->toBeTrue()
        ->and(Schema::hasColumn('subscriptions', 'active_subscriber_guard'))->toBeTrue()
        ->and(Schema::hasIndex('subscriptions', ['subscriber_type', 'active_subscriber_guard'], 'unique'))->toBeTrue();
});

it('uses final create migrations instead of fresh-install follow-ups', function (): void {
    $rootMigrations = glob(database_path('migrations/*.php')) ?: [];
    $packageMigrations = glob(base_path('packages/*/database/migrations/*.stub')) ?: [];
    $followUpMigrations = array_filter(
        [...$rootMigrations, ...$packageMigrations],
        static fn (string $path): bool => preg_match('/(?:^|_)(?:add|rename|backfill|enforce)_/', basename($path)) === 1,
    );

    expect(array_values($followUpMigrations))->toBe([]);
});

it('keeps package migration stubs identical to application baselines', function (): void {
    $rootMigrations = collect(glob(database_path('migrations/*.php')) ?: [])
        ->keyBy(fn (string $migration): string => preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', basename($migration)) ?? basename($migration));

    $packageMigrations = glob(base_path('packages/*/database/migrations/*.stub')) ?: [];

    expect($packageMigrations)->not->toBeEmpty();

    foreach ($packageMigrations as $packageMigration) {
        $migrationName = str_replace('.stub', '', basename($packageMigration));
        $rootMigration = $rootMigrations->get($migrationName);

        expect($rootMigration)
            ->not->toBeNull("Missing application baseline for [{$packageMigration}].")
            ->and(file_get_contents($packageMigration))
            ->toBe(file_get_contents($rootMigration), "Package migration [{$packageMigration}] has drifted from its application baseline.");
    }
});

/*
 | The registry drives `vendra-tenant:enable`, which backfills every null tenant
 | id and then forces the column NOT NULL. `settings` is excluded because its
 | null `tenant_id` is the platform scope and must stay null; `store_domains` and
 | `store_user` are keyed by `store_id` instead.
 */
it('registers every tenant-aware application table for legacy schema retrofits', function (): void {
    $registeredTables = collect(app(TenantTableRegistry::class)->all())
        ->where('connection', null)
        ->pluck('table')
        ->values();

    $tenantAwareTables = collect(Schema::getTableListing(schemaQualified: false))
        ->filter(fn (string $table): bool => Schema::hasColumn($table, 'tenant_id'))
        ->reject(fn (string $table): bool => in_array($table, ['settings', 'store_domains', 'store_user'], true))
        ->values();

    expect($registeredTables->all())->toEqualCanonicalizing($tenantAwareTables->all());
});

it('keeps corrected SQL types in package and application baselines', function (string $migration, array $declarations): void {
    $contents = file_get_contents(base_path($migration));

    expect($contents)->toBeString();

    foreach ($declarations as $declaration) {
        expect($contents)->toContain($declaration);
    }
})->with([
    'package currency' => [
        'packages/vendra-currency/database/migrations/create_currencies_table.php.stub',
        [
            "string('code', 16)",
            "unsignedTinyInteger('decimal_places')",
        ],
    ],
    'application currency' => [
        'database/migrations/0001_01_01_000017_create_currencies_table.php',
        [
            "string('code', 16)",
            "unsignedTinyInteger('decimal_places')",
        ],
    ],
    'package transaction gateway' => [
        'packages/vendra-transaction/database/migrations/create_transactions_table.php.stub',
        ["json('name')", "json('description')", "string('slug')"],
    ],
    'application transaction gateway' => [
        'database/migrations/0001_01_01_000018_create_transactions_table.php',
        ["json('name')", "json('description')", "string('slug')"],
    ],
    'package product price' => [
        'packages/vendra-product/database/migrations/create_products_table.php.stub',
        ["char('currency_code', 3)"],
    ],
    'application product price' => [
        'database/migrations/0001_01_01_000012_create_products_table.php',
        ["char('currency_code', 3)"],
    ],
]);
