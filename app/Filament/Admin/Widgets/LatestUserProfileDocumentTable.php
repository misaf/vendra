<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Termehsoft\User\Models\UserProfileDocument;

final class LatestUserProfileDocumentTable extends BaseWidget
{
    protected int | string | array $columnSpan = '1';

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('widget.latest_user_profile_document_table'))
            ->query(UserProfileDocument::take(10))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->extraImgAttributes(['class' => 'saturate-50'])
                    ->label(__('form.image')),

                Tables\Columns\TextColumn::make('userProfile.fullName')
                    ->label(__('model.user_profile')),

                Tables\Columns\TextColumn::make('verified_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->jalaliDate('Y-m-d H:i')
                    ->label(__('form.verified_at')),
            ])
            ->groups([
                Tables\Grouping\Group::make('verified_at')
                    ->date()
                    ->label(__('form.verified_at')),

            ])
            ->defaultSort('id', 'desc')
            ->paginated(false);
    }
}
