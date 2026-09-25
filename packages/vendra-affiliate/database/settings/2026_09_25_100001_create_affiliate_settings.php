<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('affiliate.commission_percent', 20);
        $this->migrator->add('affiliate.signup_bounty', 0);
        $this->migrator->add('affiliate.deposit_conversions', true);
        $this->migrator->add('affiliate.signup_conversions', false);
        $this->migrator->add('affiliate.checkout_conversions', false);
        $this->migrator->add('affiliate.auto_approve_commissions', true);
        $this->migrator->add('affiliate.cookie_ttl_days', 30);
        $this->migrator->add('affiliate.redirect_url', '/');
        $this->migrator->add('affiliate.payout_minimum', 1000);
    }
};
