<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class NewsletterSubscriberOverviewWidget extends StatsOverviewWidget
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
        $subscribers = NewsletterSubscriber::query();
        $subscribedSubscribers = NewsletterSubscriber::query()->subscribed();
        $unsubscribedSubscribers = NewsletterSubscriber::query()->unsubscribed();
        $subscriberTrend = Trend::query(clone $subscribers)->between($startDate, $endDate)->perDay()->count();
        $subscribedTrend = Trend::query(clone $subscribedSubscribers)->between($startDate, $endDate)->perDay()->count();
        $unsubscribedTrend = Trend::query(clone $unsubscribedSubscribers)->between($startDate, $endDate)->perDay()->count();

        return [
            Stat::make('total_newsletter_subscriber_stats', Number::format($subscribers->count()))
                ->chart($this->chartValues($subscriberTrend))
                ->color('primary')
                ->description(__('vendra-newsletter::widgets.total_subscribers_description'))
                ->descriptionIcon(Heroicon::OutlinedUserGroup, IconPosition::Before)
                ->icon(Heroicon::OutlinedEnvelope)
                ->label(__('vendra-newsletter::widgets.total_subscribers')),
            Stat::make('subscribed_newsletter_subscriber_stats', Number::format($subscribedSubscribers->count()))
                ->chart($this->chartValues($subscribedTrend))
                ->color('success')
                ->description(__('vendra-newsletter::widgets.subscribed_subscribers_description'))
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->icon(Heroicon::OutlinedEnvelope)
                ->label(__('vendra-newsletter::widgets.subscribed_subscribers')),
            Stat::make('unsubscribed_newsletter_subscriber_stats', Number::format($unsubscribedSubscribers->count()))
                ->chart($this->chartValues($unsubscribedTrend))
                ->color('danger')
                ->description(__('vendra-newsletter::widgets.unsubscribed_subscribers_description'))
                ->descriptionIcon(Heroicon::OutlinedXCircle, IconPosition::Before)
                ->icon(Heroicon::OutlinedEnvelope)
                ->label(__('vendra-newsletter::widgets.unsubscribed_subscribers')),
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
