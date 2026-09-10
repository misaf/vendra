<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Models\Newsletter;

final class NewsletterOverviewWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();
        $draftNewsletters = Newsletter::query()->where('status', NewsletterStatusEnum::Draft);
        $scheduledNewsletters = Newsletter::query()->where('status', NewsletterStatusEnum::Scheduled);
        $sentNewsletters = Newsletter::query()->where('status', NewsletterStatusEnum::Sent);
        $draftTrend = Trend::query(clone $draftNewsletters)->between($startDate, $endDate)->perDay()->count();
        $scheduledTrend = Trend::query(clone $scheduledNewsletters)->between($startDate, $endDate)->perDay()->count();
        $sentTrend = Trend::query(clone $sentNewsletters)->between($startDate, $endDate)->perDay()->count();

        return [
            Stat::make('draft_newsletter_stats', Number::format($draftNewsletters->count()))
                ->chart($this->chartValues($draftTrend))
                ->color('gray')
                ->description(__('vendra-newsletter::widgets.draft_newsletters_description'))
                ->descriptionIcon(Heroicon::OutlinedPencilSquare, IconPosition::Before)
                ->icon(Heroicon::OutlinedEnvelope)
                ->label(__('vendra-newsletter::widgets.draft_newsletters')),
            Stat::make('scheduled_newsletter_stats', Number::format($scheduledNewsletters->count()))
                ->chart($this->chartValues($scheduledTrend))
                ->color('warning')
                ->description(__('vendra-newsletter::widgets.scheduled_newsletters_description'))
                ->descriptionIcon(Heroicon::OutlinedClock, IconPosition::Before)
                ->icon(Heroicon::OutlinedEnvelope)
                ->label(__('vendra-newsletter::widgets.scheduled_newsletters')),
            Stat::make('sent_newsletter_stats', Number::format($sentNewsletters->count()))
                ->chart($this->chartValues($sentTrend))
                ->color('success')
                ->description(__('vendra-newsletter::widgets.sent_newsletters_description'))
                ->descriptionIcon(Heroicon::OutlinedPaperAirplane, IconPosition::Before)
                ->icon(Heroicon::OutlinedEnvelope)
                ->label(__('vendra-newsletter::widgets.sent_newsletters')),
        ];
    }

    /**
     * @param  Collection<(int|string), mixed>  $values
     * @return list<float>
     */
    private function chartValues(Collection $values): array
    {
        $chart = [];

        foreach ($values as $value) {
            if ($value instanceof TrendValue && is_numeric($value->aggregate)) {
                $chart[] = (float) $value->aggregate;
            }
        }

        return $chart;
    }
}
