<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Widgets;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Misaf\VendraMultimedia\Models\Multimedia;

final class LatestMultimediaTableWidget extends BaseWidget
{
    protected static ?int $sort = 6;

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
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('vendra-multimedia::widgets.recent_multimedia_table'))
            ->query(fn (): Builder => Multimedia::query())
            ->columns([
                BadgeableColumn::make('model_type')
                    ->alignStart()
                    ->label(__('vendra-multimedia::tables.model_type'))
                    ->suffixBadges([
                        Badge::make('count')
                            ->label(fn (Multimedia $record): string => Number::format((int) $record->model_id) ?: '0')
                            ->size(Size::Small),
                    ])
                    ->suffix(''),

                BadgeableColumn::make('collection_name')
                    ->alignStart()
                    ->label(__('vendra-multimedia::tables.collection_name'))
                    ->suffixBadges([
                        Badge::make('count')
                            ->label(fn (Multimedia $record): string => Number::fileSize($record->size))
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
