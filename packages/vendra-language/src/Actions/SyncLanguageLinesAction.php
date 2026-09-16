<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraLanguage\Support\TranslationCatalog;

final readonly class SyncLanguageLinesAction
{
    public function __construct(
        private TranslationCatalog $catalog,
    ) {}

    /**
     * @return array{created: int, updated: int, unchanged: int}
     */
    public function execute(): array
    {
        return DB::transaction(function (): array {
            $catalogLines = $this->catalog->languageLines();
            $namespaces = array_values(array_unique(array_column($catalogLines, 'namespace')));
            $existingLanguageLines = LanguageLine::query()
                ->whereIn('namespace', $namespaces)
                ->get()
                ->keyBy(
                    fn (LanguageLine $languageLine): string => "{$languageLine->namespace}\0{$languageLine->group}\0{$languageLine->key}",
                );
            $created = 0;
            $updated = 0;
            $unchanged = 0;

            foreach ($catalogLines as $catalogLine) {
                $identity = implode("\0", [Arr::get($catalogLine, 'namespace'), Arr::get($catalogLine, 'group'), Arr::get($catalogLine, 'key')]);
                $languageLine = $existingLanguageLines->get($identity);

                if (! $languageLine instanceof LanguageLine) {
                    LanguageLine::query()->create($catalogLine);
                    $created++;

                    continue;
                }

                $text = $languageLine->text;
                $mergedText = $text + Arr::get($catalogLine, 'text');

                if ($mergedText === $text) {
                    $unchanged++;

                    continue;
                }

                $languageLine->update(['text' => $mergedText]);
                $updated++;
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'unchanged' => $unchanged,
            ];
        });
    }
}
