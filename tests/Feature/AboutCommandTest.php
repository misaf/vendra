<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

it('reports the installed version of every Vendra package', function (): void {
    Artisan::call('about', ['--json' => true]);

    /** @var array<string, array<string, string|null>> $about */
    $about = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    $packages = [
        'Vendra Activity Log' => 'misaf/vendra-activity-log',
        'Vendra Affiliate' => 'misaf/vendra-affiliate',
        'Vendra Affiliate API' => 'misaf/vendra-affiliate-api',
        'Vendra API' => 'misaf/vendra-api',
        'Vendra Attribute' => 'misaf/vendra-attribute',
        'Vendra Attribute API' => 'misaf/vendra-attribute-api',
        'Vendra Authify Log' => 'misaf/vendra-authify-log',
        'Vendra Blog' => 'misaf/vendra-blog',
        'Vendra Blog API' => 'misaf/vendra-blog-api',
        'Vendra Cart' => 'misaf/vendra-cart',
        'Vendra Cart API' => 'misaf/vendra-cart-api',
        'Vendra Currency' => 'misaf/vendra-currency',
        'Vendra Custom Page' => 'misaf/vendra-custom-page',
        'Vendra Custom Page API' => 'misaf/vendra-custom-page-api',
        'Vendra Developer Logins' => 'misaf/vendra-developer-logins',
        'Vendra Faq' => 'misaf/vendra-faq',
        'Vendra Faq API' => 'misaf/vendra-faq-api',
        'Vendra Language' => 'misaf/vendra-language',
        'Vendra Multimedia' => 'misaf/vendra-multimedia',
        'Vendra Multimedia API' => 'misaf/vendra-multimedia-api',
        'Vendra Newsletter' => 'misaf/vendra-newsletter',
        'Vendra Permission' => 'misaf/vendra-permission',
        'Vendra Product' => 'misaf/vendra-product',
        'Vendra Product API' => 'misaf/vendra-product-api',
        'Vendra Socialite' => 'misaf/vendra-socialite',
        'Vendra Subscription' => 'misaf/vendra-subscription',
        'Vendra Tagger' => 'misaf/vendra-tagger',
        'Vendra Tenant' => 'misaf/vendra-tenant',
        'Vendra Transaction' => 'misaf/vendra-transaction',
        'Vendra User' => 'misaf/vendra-user',
        'Vendra User Profile' => 'misaf/vendra-user-profile',
    ];

    foreach ($packages as $section => $package) {
        expect($about[Str::snake($section)]['version'])
            ->toBe(InstalledVersions::getPrettyVersion($package));
    }

    expect(Artisan::output())->not->toContain('dev-master');
});
