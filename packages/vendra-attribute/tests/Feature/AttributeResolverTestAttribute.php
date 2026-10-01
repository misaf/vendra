<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Tests\Feature;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]
#[Table(name: 'support_test_attributes')]
#[WithoutTimestamps]
final class AttributeResolverTestAttribute extends Model
{
    use HasFactory;
}
