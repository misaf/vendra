<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Misaf\VendraAffiliate\Actions\AttributeReferralAction;
use Misaf\VendraUser\Models\User;

/**
 * Attributes a new registration to the affiliate referral cookie set by the
 * click-redirect route. Runs synchronously so it can read the current
 * request; the attribution itself is queued.
 */
final class RegistrationSubscriber
{
    public function __construct(
        private readonly AttributeReferralAction $attributeReferral,
    ) {}

    public function userCreated(User $user): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $cookie = $this->referralCookie();

        if ($cookie === null) {
            return;
        }

        [$code, $clickId] = $cookie;

        $this->attributeReferral->onQueue()->execute($code, $user, $clickId);
    }

    /**
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            'eloquent.created: '.User::class => 'userCreated',
        ];
    }

    /**
     * Parses the `code|clickId` referral cookie from the current request.
     *
     * @return array{0: string, 1: int|null}|null
     */
    private function referralCookie(): ?array
    {
        $request = app(Request::class);
        $value = $request->cookies->get(Config::string('vendra-affiliate.attribution.cookie_name', 'vendra_affiliate_ref'));

        if (! is_string($value) || $value === '') {
            return null;
        }

        [$code, $clickId] = array_pad(explode('|', $value, 2), 2, null);

        if (! is_string($code) || $code === '') {
            return null;
        }

        return [$code, is_numeric($clickId) ? (int) $clickId : null];
    }
}
