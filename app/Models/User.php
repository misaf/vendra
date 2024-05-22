<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Models\Tenant\Tenant;
use App\Models\User\UserProfile;
use App\Models\User\UserProfileDocument;
use App\Models\User\UserProfilePhone;
use App\Traits\BelongsToTenant;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ScopedBy(TenantScope::class)]
final class User extends AuthUser implements
    FilamentUser,
    HasName
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
     * @param Panel $panel
     *
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole('super-admin', 'admin', 'user');
    }

    /**
     * @return string
     */
    public function getFilamentName(): string
    {
        return $this->name ?? $this->email;
    }

    /**
     * @return BelongsToMany
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }

    /**
     * @return HasOne
     */
    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class)->latestOfMany();
    }

    /**
     * @return HasOneThrough
     */
    public function userProfileDocument(): HasOneThrough
    {
        return $this->hasOneThrough(UserProfileDocument::class, UserProfile::class)->latest();
    }

    /**
     * @return HasManyThrough
     */
    public function userProfileDocuments(): HasManyThrough
    {
        return $this->hasManyThrough(UserProfileDocument::class, UserProfile::class);
    }

    /**
     * @return HasOneThrough
     */
    public function userProfilePhone(): HasOneThrough
    {
        return $this->hasOneThrough(UserProfilePhone::class, UserProfile::class)->latest();
    }

    /**
     * @return HasManyThrough
     */
    public function userProfilePhones(): HasManyThrough
    {
        return $this->hasManyThrough(UserProfilePhone::class, UserProfile::class);
    }

    /**
     * @return HasMany
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
