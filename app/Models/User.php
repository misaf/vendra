<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\User\UserProfile;
use App\Models\User\UserProfileDocument;
use App\Models\User\UserProfilePhone;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    use HasFactory;

    use HasRoles;

    use Notifiable;

    use SoftDeletes;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    protected $fillable = [
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

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class)->latestOfMany();
    }

    public function userProfileDocument(): HasOneThrough
    {
        return $this->hasOneThrough(UserProfileDocument::class, UserProfile::class)->latest();
    }

    public function userProfileDocuments(): HasManyThrough
    {
        return $this->hasManyThrough(UserProfileDocument::class, UserProfile::class);
    }

    public function userProfilePhone(): HasOneThrough
    {
        return $this->hasOneThrough(UserProfilePhone::class, UserProfile::class)->latest();
    }

    public function userProfilePhones(): HasManyThrough
    {
        return $this->hasManyThrough(UserProfilePhone::class, UserProfile::class);
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
