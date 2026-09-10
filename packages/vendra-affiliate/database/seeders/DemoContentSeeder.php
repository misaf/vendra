<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Database\Seeders;

use Illuminate\Support\Facades\Validator;
use Misaf\VendraAffiliate\Database\Factories\AffiliateClickFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateCommissionFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateReferralFactory;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;
use Misaf\VendraSupport\Tenancy\RequiresCurrentTenant;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    use RequiresCurrentTenant;

    protected function seedFactories(): void
    {
        $this->currentTenantOrNull();

        AffiliateFactory::new()
            ->active()
            ->count(3)
            ->create()
            ->each(function (Affiliate $affiliate): void {
                AffiliateClickFactory::new()
                    ->forAffiliate($affiliate)
                    ->count(10)
                    ->create();

                AffiliateReferralFactory::new()
                    ->forAffiliate($affiliate)
                    ->count(2)
                    ->create()
                    ->each(fn ($referral): mixed => AffiliateCommissionFactory::new()
                        ->forAffiliate($affiliate)
                        ->approved()
                        ->state(['affiliate_referral_id' => $referral->id])
                        ->create());
            });
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        $this->currentTenantOrNull();

        foreach ($records as $record) {
            $this->seedFixtureRecord($record);
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected function seedFixtureRecord(array $record): void
    {
        $this->handleSeedFixtureRecord($this->validatedFixtureRecord($record));
    }

    /**
     * @param array{
     *     user_id: int,
     *     code: string,
     *     commission_percent: int,
     *     status: string,
     * } $record
     */
    private function handleSeedFixtureRecord(array $record): void
    {
        Affiliate::firstOrCreate(
            ['code' => $record['code']],
            [
                'user_id' => $record['user_id'],
                'commission_percent' => $record['commission_percent'],
                'status' => AffiliateStatusEnum::from($record['status']),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     user_id: int,
     *     code: string,
     *     commission_percent: int,
     *     status: string,
     * }
     */
    private function validatedFixtureRecord(array $record): array
    {
        /** @var array{user_id: int, code: string, commission_percent: int, status: string} */
        return Validator::validate($record, [
            'user_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:16'],
            'commission_percent' => ['required', 'integer', 'between:0,100'],
            'status' => ['required', 'string', 'in:active,suspended'],
        ]);
    }
}
