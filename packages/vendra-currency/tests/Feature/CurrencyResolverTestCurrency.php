<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Tests\Feature;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]
#[Table(name: 'support_test_currencies')]
#[WithoutTimestamps]
final class CurrencyResolverTestCurrency extends Model
{
    use HasFactory;
}
