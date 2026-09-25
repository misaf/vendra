<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Tests\Fixtures;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table(name: 'multimedia_test_galleries')]
final class Gallery extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public static function createTable(): void
    {
        Schema::create('multimedia_test_galleries', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
    }
}
