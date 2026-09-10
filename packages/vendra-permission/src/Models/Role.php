<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Misaf\VendraPermission\Database\Factories\RoleFactory;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string|null $description
 * @property string $guard_name
 */
#[Fillable(['name', 'description', 'guard_name'])]
#[Hidden(['tenant_id'])]
#[UseFactory(RoleFactory::class)]
final class Role extends SpatieRole implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'guard_name' => 'string',
        ];
    }
}
