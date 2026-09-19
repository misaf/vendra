# Vendra Affiliate

A full affiliate program for Vendra: referral codes and links, click tracking,
signup attribution, a per-conversion commission ledger, and payout settlement
into the `misaf/vendra-transaction` wallet — with a Filament admin cluster and
stats widgets.

## Features

1. Each affiliate gets a unique referral code and a `/r/{code}` redirect link.
2. Visiting the link records an `AffiliateClick` and drops an attribution
   cookie (`code|clickId`).
3. When a visitor registers, the referral cookie binds them to the affiliate
   (`AffiliateReferral`) — each user is attributed at most once and
   self-referrals are ignored.
4. Conversions credit `AffiliateCommission` ledger entries idempotently:
   - **Deposit** — a referred user's approved deposit credits
     `commission_percent` of the amount; leaving the approved state reverses
     the unpaid commission.
   - **Signup** — a fixed bounty per attributed registration.
   - **Checkout** — host applications call `RecordCartConversionAction` from their
     checkout flow (vendra-cart has no checkout event yet).
5. `ProcessAffiliatePayoutAction` settles approved commissions atomically: it
   groups them into an `AffiliatePayout`, marks them paid, and credits the
   affiliate's default-currency wallet through an approved Commission
   transaction. If the transaction cannot be created or settled, everything
   rolls back and the commissions remain payable.

Each conversion type is toggled in `config/vendra-affiliate.php`, alongside
the attribution cookie, payout minimum, and defaults.

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`
- `misaf/vendra-transaction`
- `misaf/vendra-user`

## Installation

```bash
composer require misaf/vendra-affiliate
php artisan vendor:publish --tag=vendra-affiliate-migrations
php artisan migrate
php artisan vendra-affiliate:seed
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-affiliate-config
php artisan vendor:publish --tag=vendra-affiliate-translations
```

## Optional tags

Install `misaf/vendra-tagger` to assign `affiliate`-typed tags from the affiliate form and display them in the table. Affiliate imports neither Vendra Tagger nor Spatie Tags; the optional relationship is resolved through `misaf/vendra-support`.

```php
use Misaf\VendraTagger\Models\Tagger;

Tagger::findOrCreate('Top performer', type: 'affiliate', locale: 'en');
```

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-affiliate
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
