<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Models\Tenant\Tenant;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// #[ScopedBy(TenantScope::class)]
final class User extends Authenticatable implements
    FilamentUser,
    HasName,
    HasTenants,
    Contracts\HasUserProfile
{
    use ActivityLog;
    use BelongsToTenant;
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use Traits\HasUserProfile;

    protected $casts = [
        'tenant_id'         => 'integer',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'created_at'        => DateCast::class,
        'updated_at'        => DateCast::class,
        'deleted_at'        => DateCast::class,
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'email_verified_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @param Panel $panel
     *
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'user']);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenants()->whereKey($tenant)->exists();
    }

    /**
     * @return string
     */
    public function getFilamentName(): string
    {
        return $this->name ?? $this->email;
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->tenants;
    }

    /**
     * @return BelongsToMany
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }
}
