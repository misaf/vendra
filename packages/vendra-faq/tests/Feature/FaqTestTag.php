<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Tests\Feature;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'tags')]
final class FaqTestTag extends Model
{
    use HasFactory;
}
