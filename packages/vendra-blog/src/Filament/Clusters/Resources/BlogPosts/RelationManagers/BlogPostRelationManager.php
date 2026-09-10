<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use LaraZeus\SpatieTranslatable\Resources\RelationManagers\Concerns\Translatable;
use Livewire\Attributes\Reactive;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\BlogPostResource;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraBlog\Models\BlogPostCategory;

final class BlogPostRelationManager extends RelationManager
{
    use Translatable;

    #[Reactive]
    public ?string $activeLocale = null;

    protected static string $relationship = 'blogPosts';

    protected static bool $isBadgeDeferred = true;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-blog::navigation.blog_post');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-blog::navigation.blog_posts');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-blog::navigation.blog_posts');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        if (! $ownerRecord instanceof BlogPostCategory) {
            return (string) Number::format(0);
        }

        if ($ownerRecord->relationLoaded('blogPosts')) {
            /** @var Collection<int, BlogPost> $blogPosts */
            $blogPosts = $ownerRecord->getRelation('blogPosts');

            return (string) Number::format($blogPosts->count());
        }

        $blogPostsCount = $ownerRecord->getAttribute('blog_posts_count');

        if (is_numeric($blogPostsCount)) {
            return (string) Number::format((int) $blogPostsCount);
        }

        return (string) Number::format(
            BlogPost::query()
                ->whereBelongsTo($ownerRecord)
                ->count()
        );
    }

    public function form(Schema $schema): Schema
    {
        return BlogPostResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return BlogPostResource::table($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
