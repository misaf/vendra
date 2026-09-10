<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Tests\Feature;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'tags')]
final class AffiliateTestTag extends Model
{
    use HasFactory;
}
