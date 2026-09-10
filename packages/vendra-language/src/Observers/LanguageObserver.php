<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Observers;

use Misaf\VendraLanguage\Models\Language;

final class LanguageObserver
{
    public function creating(Language $language): void
    {
        if (! $language->active) {
            $language->is_default = false;

            return;
        }

        if (! Language::query()->active()->exists()) {
            $language->is_default = true;
        }
    }

    public function saving(Language $language): void
    {
        if (! $language->active) {
            $language->is_default = false;

            return;
        }

        if ($language->is_default) {
            Language::query()
                ->where('is_default', true)
                ->whereKeyNot($language->getKey())
                ->update(['is_default' => false]);

            return;
        }

        if ($language->exists && $language->getOriginal('is_default') === true) {
            $hasAnotherDefault = Language::query()
                ->active()
                ->where('is_default', true)
                ->whereKeyNot($language->getKey())
                ->exists();

            if (! $hasAnotherDefault) {
                $language->is_default = true;
            }
        }
    }

    public function saved(Language $language): void
    {
        if ($language->wasChanged(['active', 'is_default'])) {
            $this->ensureActiveDefault();
        }
    }

    public function deleted(Language $language): void
    {
        if (! $language->is_default) {
            return;
        }

        $this->ensureActiveDefault();
    }

    private function ensureActiveDefault(): void
    {
        if (Language::query()->active()->where('is_default', true)->exists()) {
            return;
        }

        Language::query()
            ->active()
            ->ordered()
            ->first()
            ?->update(['is_default' => true]);
    }
}
