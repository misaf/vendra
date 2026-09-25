<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\IsDefaultToggle;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;

final class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('locale')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.locale'))
                    ->columnSpanFull()
                    ->label(__('vendra-language::attributes.locale'))
                    ->live()
                    ->native(false)
                    ->options(fn (?Language $record): array => self::installableLocaleOptions($record))
                    ->required()
                    ->rule(Rule::in(Locales::all()))
                    ->searchable()
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule): Unique => self::constrainToCurrentTenant($rule),
                    ),

                IsActiveToggle::make()
                    ->default(false),

                IsDefaultToggle::make()
                    ->helperText(__('vendra-language::attributes.is_default_helper_text')),
            ]);
    }

    /** @return array<string, string> */
    private static function installableLocaleOptions(?Language $record): array
    {
        $installedLanguagesQuery = TenantAwareness::constrainToCurrentTenant(Language::query());

        if ($record !== null) {
            $installedLanguagesQuery->whereKeyNot($record->getKey());
        }

        $installedLocales = $installedLanguagesQuery
            ->get(['locale'])
            ->map(fn (Language $language): string => $language->locale);

        return collect(Locales::options())
            ->except($installedLocales)
            ->all();
    }

    private static function constrainToCurrentTenant(Unique $rule): Unique
    {
        if (TenantAwareness::enabled() && TenantAwareness::currentId() === null) {
            return $rule->whereNull(TenantSchema::column());
        }

        return TenantAwareness::constrainUniqueRule($rule);
    }
}
