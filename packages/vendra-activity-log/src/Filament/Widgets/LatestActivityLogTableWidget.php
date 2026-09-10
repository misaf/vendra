<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Filament\Widgets;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Misaf\VendraActivityLog\Models\ActivityLog;

final class LatestActivityLogTableWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 1;
    }

    public static function isDiscovered(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('vendra-activity-log::widgets.recent_activity_log_table'))
            ->query(fn (): Builder => ActivityLog::query())
            ->columns([
                BadgeableColumn::make('subject_type')
                    ->alignStart()
                    ->label(__('vendra-activity-log::tables.subject_type'))
                    ->suffixBadges([
                        Badge::make('count')
                            ->label(fn (ActivityLog $record): string => Number::format((int) $record->subject_id) ?: '0')
                            ->size(Size::Small),
                    ])
                    ->suffix(''),

                BadgeableColumn::make('causer_type')
                    ->alignStart()
                    ->label(__('vendra-activity-log::tables.causer_type'))
                    ->suffixBadges([
                        Badge::make('count')
                            ->label(fn (ActivityLog $record): string => Number::format((int) $record->causer_id) ?: '0')
                            ->size(Size::Small),
                    ])
                    ->suffix(''),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort(column: 'id', direction: 'desc')
            ->paginationPageOptions([5]);
    }
}
