<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUser\Traits\BelongsToUser;
use Misaf\VendraUserProfile\Database\Factories\UserProfileFactory;
use Misaf\VendraUserProfile\Observers\UserProfileObserver;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string $slug
 * @property bool $is_default
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['tenant_id', 'user_id', 'name', 'description', 'slug', 'is_default', 'active'])]
#[Hidden(['tenant_id', 'active_name_guard', 'active_slug_guard', 'default_user_guard'])]
#[ObservedBy([UserProfileObserver::class])]
#[UseFactory(UserProfileFactory::class)]
final class UserProfile extends Model implements ShouldLogActivity
{
    use BelongsToTenant;
    use BelongsToUser;

    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    use HasSlug;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'user_id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'slug' => 'string',
            'is_default' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->name,
        );
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
