<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\Faqs;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Pages\CreateFaq;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Pages\EditFaq;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Pages\ListFaqs;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Pages\ViewFaq;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Schemas\FaqForm;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Schemas\FaqInfolist;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Tables\FaqTable;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraSupport\Filament\Clusters\ContentCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class FaqResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?int $navigationSort = NavigationPriority::Faqs->value;

    protected static ?string $slug = 'faqs';

    protected static ?string $cluster = ContentCluster::class;

    /**
     * @return array<int, string>
     */
    protected static function translatableGlobalSearchAttributes(): array
    {
        return ['name', 'slug'];
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-faq::navigation.faq');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-faq::navigation.faq');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-faq::navigation.faqs');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-faq::navigation.faqs');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'view' => ViewFaq::route('/{record}'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return FaqForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FaqInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqTable::configure($table);
    }
}
