<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Misaf\LaravelAuthifyLog\Enums\AuthifyLogActionEnum;
use Misaf\VendraAuthifyLog\Models\AuthifyLog;

final class LatestAuthifyLogTableWidget extends BaseWidget
{
    protected static ?int $sort = 9;

    /**
     * @var int|string|array<string, int|null>
     */
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
            ->heading(__('vendra-authify-log::widgets.recent_authify_log_table'))
            ->query(AuthifyLog::query()->where('action', '<>', AuthifyLogActionEnum::Authenticated)->take(5))
            ->columns([
                TextColumn::make('action')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-authify-log::attributes.action'))
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->alignCenter()
                    ->copyable()
                    ->copyMessage(__('vendra-authify-log::messages.ip_address_copied'))
                    ->copyMessageDuration(1500)
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-authify-log::attributes.ip_address'))
                    ->formatStateUsing(fn(string $state, AuthifyLog $record): HtmlString => new HtmlString(
                        '<span class="flex items-center space-x-2">'
                        .'<img src="'.asset('vendor/blade-country-flags/4x3-'.Str::lower($record->ip_country).'.svg').'" alt="'.$record->ip_country.'" title="'.$record->ip_country.'" class="w-4 inline-block" />'
                        .'<span>'.$state.'</span>'
                        .'</span>',
                    )),

                TextColumn::make('created_at')
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('vendra-authify-log::attributes.created_at'))
                    ->sinceTooltip()
                    ->when(app()->isLocale('fa'), fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d', latinNumbers: true)),
            ])
            ->searchable(false)
            ->paginated(false);
    }
}
