<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Misaf\LaravelAuthifyLog\Enums\AuthifyLogActionEnum;
use Misaf\VendraAuthifyLog\Database\Factories\AuthifyLogFactory;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property AuthifyLogActionEnum $action
 * @property string $ip_address
 * @property string $ip_country
 * @property string $user_agent
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['tenant_id', 'user_id', 'action', 'ip_address', 'ip_country', 'user_agent'])]
#[Hidden(['tenant_id'])]
#[UseFactory(AuthifyLogFactory::class)]
final class AuthifyLog extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AuthifyLogFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'user_id' => 'integer',
            'action' => AuthifyLogActionEnum::class,
            'ip_address' => 'string',
            'ip_country' => 'string',
            'user_agent' => 'string',
        ];
    }
}
