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
            $result = [
                'created' => 0,
                'updated' => 0,
                'unchanged' => 0,
            ];

            foreach ($catalogLines as $catalogLine) {
                $identity = "{Arr::get($catalogLine, 'namespace')}\0{Arr::get($catalogLine, 'group')}\0{Arr::get($catalogLine, 'key')}";
                $languageLine = $existingLanguageLines->get($identity);

                if (! $languageLine instanceof LanguageLine) {
                    LanguageLine::query()->create($catalogLine);
                    Arr::get($result, 'created')++;

                    continue;
                }

                $text = $languageLine->text;
                $mergedText = $text + Arr::get($catalogLine, 'text');

                if ($mergedText === $text) {
                    Arr::get($result, 'unchanged')++;

                    continue;
                }

                $languageLine->update(['text' => $mergedText]);
                Arr::get($result, 'updated')++;
            }

            return $result;
        });
    }
}
