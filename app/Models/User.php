<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\DateCast;
use App\Traits\BelongsToTenant;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

final class User extends \Illuminate\Foundation\Auth\User implements
    \Filament\Models\Contracts\FilamentUser,
    \Filament\Models\Contracts\HasName,
    \Filament\Models\Contracts\HasTenants
{
    use BelongsToTenant;

    use HasApiTokens;

    use HasFactory;

    use HasRoles;

    use Notifiable;

    use SoftDeletes;

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

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole('super-admin', 'admin', 'user');
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenants()->whereKey($tenant)->exists();
    }

    public function getFilamentName(): string
    {
        return $this->name ?? $this->email;
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->tenants;
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            related: \App\Models\Tenant\Tenant::class,
        );
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(
            related: \App\Models\User\UserProfile::class,
        )->latestOfMany();
    }

    public function userProfileDocument(): HasOneThrough
    {
        return $this->hasOneThrough(
            related: \App\Models\User\UserProfileDocument::class,
            through: \App\Models\User\UserProfile::class,
        )->latest();
    }

    public function userProfileDocuments(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: \App\Models\User\UserProfileDocument::class,
            through: \App\Models\User\UserProfile::class,
        );
    }

    public function userProfilePhone(): HasOneThrough
    {
        return $this->hasOneThrough(
            related: \App\Models\User\UserProfilePhone::class,
            through: \App\Models\User\UserProfile::class,
        )->latest();
    }

    public function userProfilePhones(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: \App\Models\User\UserProfilePhone::class,
            through: \App\Models\User\UserProfile::class,
        );
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\User\UserProfile::class,
        );
    }
}
