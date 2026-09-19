<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Policies;

use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;
use Misaf\VendraSupport\Authorization\AuthorizesViewAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;
use Misaf\VendraTransaction\Enums\WalletPolicyEnum;

/**
 * Ledger entries are read-only and governed by the wallet permissions.
 */
final class LedgerEntryPolicy
{
    use AuthorizesSandboxMode;
    use AuthorizesViewAbilities;
    use ResolvesPolicyPermissions;

    protected static function permissionEnum(): string
    {
        return WalletPolicyEnum::class;
    }
}
