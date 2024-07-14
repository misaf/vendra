<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Number;
use Termehsoft\Blog\Models\BlogPost;
use Termehsoft\Faq\Models\Faq;
use Termehsoft\User\Models\User;

final class StatOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $userStats = Trend::model(User::class)
            ->between(
                start: now()->startOfWeek(),
                end: now()->endOfWeek(),
            )
            ->perDay()
            ->count();

        $blogPostStats = Trend::model(BlogPost::class)
            ->between(
                start: now()->startOfWeek(),
                end: now()->endOfWeek(),
            )
            ->perDay()
            ->count();

        $faqStats = Trend::model(Faq::class)
            ->between(
                start: now()->startOfWeek(),
                end: now()->endOfWeek(),
            )
            ->perDay()
            ->count();

        return [
            Stat::make('Blog Post', Number::format(User::count()))
                ->label(__('model.user'))
                ->description('32% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($userStats->map(fn(TrendValue $value) => $value->aggregate)->toArray())
                ->color('primary'),

            Stat::make('Blog Post', Number::format(BlogPost::count()))
                ->label(__('model.blog_post'))
                ->description('32% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($blogPostStats->map(fn(TrendValue $value) => $value->aggregate)->toArray())
                ->color('primary'),

            Stat::make('Faq', Number::format(Faq::count()))
                ->label(__('model.faq'))
                ->description('3% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($faqStats->map(fn(TrendValue $value) => $value->aggregate)->toArray())
                ->color('primary'),
        ];
    }
}
