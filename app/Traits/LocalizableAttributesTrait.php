<?php

declare(strict_types=1);

namespace App\Traits;

use LaravelJsonApi\Core\Support\Arr;

trait LocalizableAttributesTrait
{
    private function getLocalizedAttribute(string $attribute, ?string $locale): string|array
    {
        return $locale ? $this->getTranslation($attribute, $locale) : Arr::camelize($this->getTranslations($attribute));
    }
}
