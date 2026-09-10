<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Tests\Feature;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'tags')]
final class BlogTestTag extends Model
{
    use HasFactory;
}
