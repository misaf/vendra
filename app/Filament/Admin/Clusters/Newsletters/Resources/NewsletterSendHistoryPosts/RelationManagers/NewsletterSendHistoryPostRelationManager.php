<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Newsletters\Resources\NewsletterSendHistoryPosts\RelationManagers;

use App\Filament\Admin\Clusters\Newsletters\Resources\NewsletterSendHistories\NewsletterSendHistoryResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use LaraZeus\SpatieTranslatable\Resources\RelationManagers\Concerns\Translatable;

final class NewsletterSendHistoryPostRelationManager extends RelationManager
{
    use Translatable;

    protected static string $relationship = 'newsletterPosts';

    public static function getModelLabel(): string
    {
        return __('newsletter::navigation.newsletter_send_history_post');
    }

    public static function getPluralModelLabel(): string
    {
        return __('newsletter::navigation.newsletter_send_history_post');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('newsletter::navigation.newsletter_send_history_post');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        return (string) Number::format($ownerRecord->newsletterPosts()->count());
    }

    public function table(Table $table): Table
    {
        return NewsletterSendHistoryResource::table($table);
    }
}
