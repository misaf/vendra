<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SupportTestTranslatedRecord extends Model
{
    use HasFactory;
    /** @var array<string, array<string, mixed>> */
    public array $recordTranslations = [];

    public function getTranslation(string $attribute, string $locale): mixed
    {
        return $this->recordTranslations[$attribute][$locale] ?? null;
    }
}
