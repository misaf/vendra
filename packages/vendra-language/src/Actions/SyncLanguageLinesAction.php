<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraLanguage\Support\TranslationCatalog;

final class SyncLanguageLinesAction
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
                $identity = "{$catalogLine['namespace']}\0{$catalogLine['group']}\0{$catalogLine['key']}";
                $languageLine = $existingLanguageLines->get($identity);

                if (! $languageLine instanceof LanguageLine) {
                    LanguageLine::query()->create($catalogLine);
                    $result['created']++;

                    continue;
                }

                $text = $languageLine->text;
                $mergedText = $text + $catalogLine['text'];

                if ($mergedText === $text) {
                    $result['unchanged']++;

                    continue;
                }

                $languageLine->update(['text' => $mergedText]);
                $result['updated']++;
            }

            return $result;
        });
    }
}
