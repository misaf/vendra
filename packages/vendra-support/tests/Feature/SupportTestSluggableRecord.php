<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Unguarded]
#[Table(name: 'support_test_sluggable_records')]
final class SupportTestSluggableRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return ['name' => 'array', 'slug' => 'array'];
    }
}
