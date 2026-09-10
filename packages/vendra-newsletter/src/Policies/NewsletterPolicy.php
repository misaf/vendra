<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Policies;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraNewsletter\Enums\NewsletterPolicyEnum;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraSupport\Authorization\AuthorizesCreateAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesForceDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesRestoreAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;
use Misaf\VendraSupport\Authorization\AuthorizesViewAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;

final class NewsletterPolicy
{
    use AuthorizesCreateAbilities;
    use AuthorizesDeleteAbilities;
    use AuthorizesForceDeleteAbilities;
    use AuthorizesRestoreAbilities;
    use AuthorizesSandboxMode;
    use AuthorizesViewAbilities;
    use ResolvesPolicyPermissions;

    protected static function permissionEnum(): string
    {
        return NewsletterPolicyEnum::class;
    }

    public function send(Authorizable $user, Newsletter $newsletter): bool
    {
        return $newsletter->status !== NewsletterStatusEnum::Sent
            && $this->allowed($user, 'Send');
    }

    public function update(Authorizable $user, Newsletter $newsletter): bool
    {
        return $newsletter->status !== NewsletterStatusEnum::Sent
            && $this->allowed($user, 'Update');
    }
}
