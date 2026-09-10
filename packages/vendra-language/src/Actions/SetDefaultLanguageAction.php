<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraLanguage\Models\Language;

final class SetDefaultLanguageAction
{
    public function execute(Language $language): void
    {
        DB::transaction(function () use ($language): void {
            Language::query()
                ->lockForUpdate()
                ->get(['id']);

            $language->update([
                'active' => true,
                'is_default' => true,
            ]);
        });
    }
}
