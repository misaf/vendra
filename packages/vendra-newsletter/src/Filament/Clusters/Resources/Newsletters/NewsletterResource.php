<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages\CreateNewsletter;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages\EditNewsletter;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages\ListNewsletters;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages\ViewNewsletter;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Schemas\NewsletterForm;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Schemas\NewsletterInfolist;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Tables\NewsletterTable;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Widgets\NewsletterOverviewWidget;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraSupport\Filament\Clusters\MarketingCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class NewsletterResource extends Resource
{
    protected static ?string $model = Newsletter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = NavigationPriority::Newsletters->value;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?string $slug = 'newsletters';

    protected static ?string $cluster = MarketingCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-newsletter::navigation.newsletter');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-newsletter::navigation.newsletter');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-newsletter::navigation.newsletters');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-newsletter::navigation.newsletters');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['subject'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletters::route('/'),
            'create' => CreateNewsletter::route('/create'),
            'view' => ViewNewsletter::route('/{record}'),
            'edit' => EditNewsletter::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            NewsletterOverviewWidget::class,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return NewsletterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NewsletterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterTable::configure($table);
    }
}
