<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\BlogPostResource;

final class ViewBlogPost extends ViewRecord
{
    use Translatable;

    protected static string $resource = BlogPostResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-blog::navigation.blog_post');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
