<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Policies;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraInquiry\Enums\InquiryPolicyEnum;
use Misaf\VendraSupport\Authorization\AuthorizesDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesForceDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesRestoreAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;
use Misaf\VendraSupport\Authorization\AuthorizesUpdateAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesViewAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;

/**
 * Enquiries only arrive from the storefront, never the admin panel.
 */
final class InquiryPolicy
{
    use AuthorizesDeleteAbilities;
    use AuthorizesForceDeleteAbilities;
    use AuthorizesRestoreAbilities;
    use AuthorizesSandboxMode;
    use AuthorizesUpdateAbilities;
    use AuthorizesViewAbilities;
    use ResolvesPolicyPermissions;

    protected static function permissionEnum(): string
    {
        return InquiryPolicyEnum::class;
    }

    public function create(Authorizable $user): bool
    {
        return false;
    }
}
