<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraTagger\Database\Factories\TaggerFactory;
use Spatie\Tags\Tag as SpatieTag;
use Stringable;

/**
 * @property int $id
 * @property int $tenant_id
 * @property array<string, string> $name
 * @property array<string, string> $slug
 * @property string|null $type
 * @property int|null $position
 */
#[Hidden(['tenant_id'])]
#[UseFactory(TaggerFactory::class)]
#[Table(name: 'tags')]
final class Tagger extends SpatieTag
{
    use BelongsToTenant;

    /**
     * @var array{order_column_name: string, sort_when_creating: bool}
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * @return array<string, Stringable|string>
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'position' => 'integer',
            ...parent::casts(),
        ];
    }

    protected function generateSlug(string $locale): string
    {
        $slug = $this->getTranslation('slug', $locale, false);

        return is_string($slug) && filled($slug)
            ? Str::slug($slug)
            : parent::generateSlug($locale);
    }
}
