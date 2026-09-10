<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Pages\ListInquiries;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Pages\ViewInquiry;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Schemas\InquiryForm;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Schemas\InquiryInfolist;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\Tables\InquiryTable;
use Misaf\VendraInquiry\Models\Inquiry;
use Misaf\VendraSupport\Filament\Clusters\CustomersCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?int $navigationSort = NavigationPriority::Inquiries->value;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'inquiries';

    protected static ?string $cluster = CustomersCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-inquiry::navigation.inquiry');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-inquiry::navigation.inquiry');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-inquiry::navigation.inquiries');
    }

    public static function getNavigationBadge(): string
    {
        return (string) Number::format(Inquiry::query()->unanswered()->count());
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return __('vendra-inquiry::navigation.navigation_badge_tooltip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-inquiry::navigation.inquiries');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'message'];
    }

    public static function form(Schema $schema): Schema
    {
        return InquiryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InquiryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
            'view' => ViewInquiry::route('/{record}'),
        ];
    }
}
