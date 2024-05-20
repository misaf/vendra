<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\DateCast;
use App\Traits\BelongsToTenant;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
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

#[ScopedBy([\App\Scopes\Tenant::class])]
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

    /**
     * @param \Filament\Panel $panel
     *
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole('super-admin', 'admin', 'user');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $tenant
     *
     * @return bool
     */
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

    /**
     * @param \Filament\Panel $panel
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->tenants;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            related: \App\Models\Tenant\Tenant::class,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userProfile(): HasOne
    {
        return $this->hasOne(
            related: \App\Models\User\UserProfile::class,
        )->latestOfMany();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function userProfileDocument(): HasOneThrough
    {
        return $this->hasOneThrough(
            related: \App\Models\User\UserProfileDocument::class,
            through: \App\Models\User\UserProfile::class,
        )->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function userProfileDocuments(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: \App\Models\User\UserProfileDocument::class,
            through: \App\Models\User\UserProfile::class,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function userProfilePhone(): HasOneThrough
    {
        return $this->hasOneThrough(
            related: \App\Models\User\UserProfilePhone::class,
            through: \App\Models\User\UserProfile::class,
        )->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function userProfilePhones(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: \App\Models\User\UserProfilePhone::class,
            through: \App\Models\User\UserProfile::class,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\User\UserProfile::class,
        );
    }
}
