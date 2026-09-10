<?php

declare(strict_types=1);

use Filament\Panel;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Misaf\VendraAffiliate\AffiliatePlugin;
use Misaf\VendraAffiliate\Database\Factories\AffiliateClickFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateCommissionFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliatePayoutFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateReferralFactory;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Widgets\AffiliateCommissionOverviewWidget;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Widgets\AffiliatePayoutOverviewWidget;
use Misaf\VendraAffiliate\Filament\Widgets\AffiliateOverviewWidget;
use Misaf\VendraAffiliate\Filament\Widgets\UserAffiliateOverviewWidget;
use Misaf\VendraUser\Database\Factories\UserFactory;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('registers admin management separately from the personal user widget', function (): void {
    $adminPanel = Panel::make()->id('admin');
    $userPanel = Panel::make()->id('user');

    AffiliatePlugin::make()->register($adminPanel);
    AffiliatePlugin::make()->register($userPanel);

    expect($adminPanel->getWidgets())->toContain(AffiliateOverviewWidget::class);
    expect(in_array(UserAffiliateOverviewWidget::class, $adminPanel->getWidgets(), true))->toBeFalse();
    expect($adminPanel->getClusters())->not->toBeEmpty();
    expect($userPanel->getWidgets())->toContain(UserAffiliateOverviewWidget::class);
    expect(in_array(AffiliateOverviewWidget::class, $userPanel->getWidgets(), true))->toBeFalse();
    expect($userPanel->getClusters())->toBeEmpty();
});

it('shows tenant-scoped affiliate metrics and earned commissions', function (): void {
    $tenant = createTestTenant();
    switchToTestTenant($tenant);

    $affiliate = AffiliateFactory::new()->createOne();

    AffiliateClickFactory::new()
        ->forAffiliate($affiliate)
        ->count(2)
        ->create();

    AffiliateReferralFactory::new()
        ->forAffiliate($affiliate)
        ->createOne();

    AffiliateCommissionFactory::new()
        ->forAffiliate($affiliate)
        ->approved()
        ->state(['amount' => 1_250])
        ->createOne();

    AffiliateCommissionFactory::new()
        ->forAffiliate($affiliate)
        ->paid()
        ->state(['amount' => 2_750])
        ->createOne();

    AffiliateCommissionFactory::new()
        ->forAffiliate($affiliate)
        ->pending()
        ->state(['amount' => 8_000])
        ->createOne();

    AffiliateCommissionFactory::new()
        ->forAffiliate($affiliate)
        ->reversed()
        ->state(['amount' => 16_000])
        ->createOne();

    $otherTenant = createTestTenant();
    switchToTestTenant($otherTenant);

    $otherAffiliate = AffiliateFactory::new()->createOne();

    AffiliateClickFactory::new()
        ->forAffiliate($otherAffiliate)
        ->count(3)
        ->create();

    AffiliateReferralFactory::new()
        ->forAffiliate($otherAffiliate)
        ->count(2)
        ->create();

    AffiliateCommissionFactory::new()
        ->forAffiliate($otherAffiliate)
        ->approved()
        ->state(['amount' => 32_000])
        ->createOne();

    switchToTestTenant($tenant);

    livewire(AffiliateOverviewWidget::class)
        ->assertSeeInOrder([
            __('vendra-affiliate::widgets.affiliate_click_stats'),
            '2',
            __('vendra-affiliate::widgets.affiliate_referral_stats'),
            '1',
            __('vendra-affiliate::widgets.affiliate_commission_stats'),
            '4,000',
        ]);

    /** @var array<int, Stat> $stats */
    $stats = (new ReflectionMethod(AffiliateOverviewWidget::class, 'getStats'))
        ->invoke(app(AffiliateOverviewWidget::class));

    expect(array_map(
        static fn (Stat $stat): mixed => $stat->getIcon(),
        $stats,
    ))->each->toBe(Heroicon::OutlinedLink);
});

it('shows tenant-scoped commission and payout resource metrics', function (): void {
    $tenant = createTestTenant();
    switchToTestTenant($tenant);

    $affiliate = AffiliateFactory::new()->createOne();

    AffiliateCommissionFactory::new()->forAffiliate($affiliate)->pending()->createOne();
    AffiliateCommissionFactory::new()->forAffiliate($affiliate)->approved()->createOne();
    AffiliateCommissionFactory::new()->forAffiliate($affiliate)->paid()->createOne();
    AffiliateCommissionFactory::new()->forAffiliate($affiliate)->reversed()->createOne();

    AffiliatePayoutFactory::new()->forAffiliate($affiliate)->count(2)->create();
    AffiliatePayoutFactory::new()->forAffiliate($affiliate)->completed()->createOne();
    AffiliatePayoutFactory::new()->forAffiliate($affiliate)->failed()->createOne();

    $otherTenant = createTestTenant();
    switchToTestTenant($otherTenant);

    $otherAffiliate = AffiliateFactory::new()->createOne();

    AffiliateCommissionFactory::new()->forAffiliate($otherAffiliate)->approved()->createOne();
    AffiliatePayoutFactory::new()->forAffiliate($otherAffiliate)->completed()->createOne();

    switchToTestTenant($tenant);

    /** @var array<int, Stat> $commissionStats */
    $commissionStats = (new ReflectionMethod(AffiliateCommissionOverviewWidget::class, 'getStats'))
        ->invoke(app(AffiliateCommissionOverviewWidget::class));

    /** @var array<int, Stat> $payoutStats */
    $payoutStats = (new ReflectionMethod(AffiliatePayoutOverviewWidget::class, 'getStats'))
        ->invoke(app(AffiliatePayoutOverviewWidget::class));

    expect(array_map(
        static fn (Stat $stat): mixed => $stat->getValue(),
        $commissionStats,
    ))->toBe(['4', '1', '1'])
        ->and(array_map(
            static fn (Stat $stat): mixed => $stat->getValue(),
            $payoutStats,
        ))->toBe(['2', '1', '1']);

    foreach ([$commissionStats, $payoutStats] as $stats) {
        expect(array_map(
            static fn (Stat $stat): mixed => $stat->getIcon(),
            $stats,
        ))->each->toBe(Heroicon::OutlinedLink)
            ->and(array_map(
                static fn (Stat $stat): IconPosition|string => $stat->getDescriptionIconPosition(),
                $stats,
            ))->each->toBe(IconPosition::Before)
            ->and(array_map(
                static fn (Stat $stat): int => count($stat->getChart() ?? []),
                $stats,
            ))->each->toBeGreaterThan(0);
    }
});

it('shows only the signed-in users affiliate metrics', function (): void {
    makeCurrentTestTenant();

    $user = UserFactory::new()->createOne();
    $affiliate = AffiliateFactory::new()->forUser($user)->createOne();

    AffiliateClickFactory::new()
        ->forAffiliate($affiliate)
        ->count(2)
        ->create();

    AffiliateReferralFactory::new()
        ->forAffiliate($affiliate)
        ->createOne();

    AffiliateCommissionFactory::new()
        ->forAffiliate($affiliate)
        ->approved()
        ->state(['amount' => 1_500])
        ->createOne();

    AffiliateCommissionFactory::new()
        ->forAffiliate($affiliate)
        ->paid()
        ->state(['amount' => 2_500])
        ->createOne();

    AffiliateCommissionFactory::new()
        ->forAffiliate($affiliate)
        ->pending()
        ->state(['amount' => 8_000])
        ->createOne();

    $otherAffiliate = AffiliateFactory::new()->createOne();

    AffiliateClickFactory::new()
        ->forAffiliate($otherAffiliate)
        ->count(3)
        ->create();

    AffiliateReferralFactory::new()
        ->forAffiliate($otherAffiliate)
        ->count(2)
        ->create();

    AffiliateCommissionFactory::new()
        ->forAffiliate($otherAffiliate)
        ->approved()
        ->state(['amount' => 32_000])
        ->createOne();

    actingAs($user);

    expect(UserAffiliateOverviewWidget::canView())->toBeTrue();

    livewire(UserAffiliateOverviewWidget::class)
        ->assertSeeInOrder([
            __('vendra-affiliate::widgets.affiliate_click_stats'),
            '2',
            __('vendra-affiliate::widgets.affiliate_referral_stats'),
            '1',
            __('vendra-affiliate::widgets.affiliate_commission_stats'),
            '4,000',
        ]);
});

it('hides the personal affiliate widget from users without an affiliate', function (): void {
    makeCurrentTestTenant();

    actingAs(UserFactory::new()->createOne());

    expect(UserAffiliateOverviewWidget::canView())->toBeFalse();
});
