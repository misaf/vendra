<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\Schemas;

use Illuminate\Support\Arr;
use Closure;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraLanguage\Support\TranslationCatalog;
use Misaf\VendraLanguage\Support\TranslationLocales;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class LanguageLineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('namespace')
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('vendra-language::attributes.namespace_help'))
                    ->label(__('vendra-language::attributes.namespace'))
                    ->live()
                    ->native(false)
                    ->options(fn (TranslationCatalog $catalog): array => $catalog->namespaceOptions())
                    ->placeholder(__('vendra-language::attributes.namespace_none'))
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(function (Livewire $livewire, Set $set): void {
                        $livewire->validateOnly('data.namespace');

                        $set('group', null);
                        $set('key', null);
                    }),

                Select::make('group')
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('vendra-language::attributes.group_help'))
                    ->label(__('vendra-language::attributes.group'))
                    ->live()
                    ->native(false)
                    ->options(fn (Get $get, TranslationCatalog $catalog): array => $catalog->groupOptions(
                        $get->string('namespace', isNullable: true),
                    ))
                    ->preload()
                    ->required()
                    ->searchable()
                    ->afterStateUpdated(function (Livewire $livewire, Set $set): void {
                        $livewire->validateOnly('data.group');

                        $set('key', null);
                    }),

                Select::make('key')
                    ->afterStateUpdated(function (Livewire $livewire): void {
                        $livewire->validateOnly('data.key');
                    })
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-language::attributes.key'))
                    ->live()
                    ->native(false)
                    ->options(fn (Get $get, TranslationCatalog $catalog): array => $catalog->keyOptions(
                        $get->string('namespace', isNullable: true),
                        $get->string('group', isNullable: true),
                    ))
                    ->preload()
                    ->required()
                    ->searchable()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, Get $get): void {
                            TenantAwareness::constrainUniqueRule($rule);

                            $group = $get->string('group', isNullable: true);

                            if ($group !== null) {
                                $rule->where('group', $group);
                            }

                            $namespace = $get->string('namespace', isNullable: true);

                            if ($namespace === null) {
                                $rule->whereNull('namespace');
                            } else {
                                $rule->where('namespace', $namespace);
                            }
                        },
                    ),

                KeyValue::make('text')
                    ->addable(false)
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.text'))
                    ->live()
                    ->afterStateHydrated(function (KeyValue $component, mixed $state): void {
                        $translations = [];

                        if (is_array($state)) {
                            foreach ($state as $locale => $translation) {
                                if (is_string($locale) && is_string($translation)) {
                                    $translations[$locale] = $translation;
                                }
                            }
                        }

                        $component->state(TranslationLocales::merge($translations));
                    })
                    ->columnSpanFull()
                    ->default(fn (): array => TranslationLocales::merge())
                    ->deletable(false)
                    ->editableKeys(false)
                    ->keyLabel(__('vendra-language::attributes.locale'))
                    ->label(__('vendra-language::attributes.text'))
                    ->reorderable(false)
                    ->required()
                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                        if (! self::hasTranslation($value)) {
                            $fail(__('vendra-language::validation.translation_required'));
                        }
                    })
                    ->valueLabel(__('vendra-language::attributes.translation')),
            ]);
    }

    private static function hasTranslation(mixed $state): bool
    {
        if (! is_array($state)) {
            return false;
        }

        foreach ($state as $translation) {
            if (is_array($translation)) {
                $translation = Arr::get($translation, 'value', null);
            }

            if (is_string($translation) && Str::of($translation)->trim()->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }
}
