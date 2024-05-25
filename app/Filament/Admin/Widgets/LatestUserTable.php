<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\User\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class LatestUserTable extends BaseWidget
{
    protected int | string | array $columnSpan = '1';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('widget.latest_user_table'))
            ->query(User::take(10))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->extraImgAttributes(['class' => 'saturate-50'])
                    ->label(__('form.image')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.name')),

                Tables\Columns\TextColumn::make('email')
                    ->alignLeft()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('form.email')),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->jalaliDate('Y-m-d H:i')
                    ->label(__('form.email_verified_at')),
            ])
            ->groups([
                Tables\Grouping\Group::make('email_verified_at')
                    ->date()
                    ->label(__('form.email_verified_at')),

            ])
            ->paginated(false);
    }
}
