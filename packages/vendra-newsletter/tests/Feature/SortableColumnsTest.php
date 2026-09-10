<?php

declare(strict_types=1);

use Filament\Tables\Filters\TernaryFilter;
use Misaf\VendraNewsletter\Database\Factories\NewsletterFactory;
use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages\ListNewsletters;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('sorts the newsletters table by every sortable column following the stored values', function (): void {
    $first = NewsletterFactory::new()->createOne();
    $second = NewsletterFactory::new()->createOne();

    expect(livewire(ListNewsletters::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('sorts the newsletter subscribers table by every sortable column following the stored values', function (): void {
    $first = NewsletterSubscriberFactory::new()->createOne();
    $second = NewsletterSubscriberFactory::new()->createOne();

    expect(livewire(ListNewsletterSubscribers::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('updates newsletter subscription state from the table toggle', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->unsubscribed()->createOne();

    livewire(ListNewsletterSubscribers::class)
        ->call('updateTableColumnState', 'subscribed', (string) $subscriber->getKey(), true);

    expect($subscriber->refresh()->isSubscribed())->toBeTrue()
        ->and($subscriber->subscribed_at)->not->toBeNull();

    livewire(ListNewsletterSubscribers::class)
        ->call('updateTableColumnState', 'subscribed', (string) $subscriber->getKey(), false);

    expect($subscriber->refresh()->isSubscribed())->toBeFalse()
        ->and($subscriber->unsubscribed_at)->not->toBeNull();
});

it('filters newsletter subscribers by localized active state', function (): void {
    $activeSubscriber = NewsletterSubscriberFactory::new()->subscribed()->createOne();
    $inactiveSubscriber = NewsletterSubscriberFactory::new()->unsubscribed()->createOne();

    $component = livewire(ListNewsletterSubscribers::class)
        ->call('loadTable')
        ->assertTableFilterExists('subscribed', fn (TernaryFilter $filter): bool => $filter->getLabel() === __('vendra-newsletter::attributes.active')
            && $filter->getTrueLabel() === __('vendra-newsletter::attributes.active')
            && $filter->getFalseLabel() === __('vendra-newsletter::attributes.inactive'))
        ->filterTable('subscribed', true)
        ->assertCanSeeTableRecords([$activeSubscriber])
        ->assertCanNotSeeTableRecords([$inactiveSubscriber]);

    $component
        ->filterTable('subscribed', false)
        ->assertCanSeeTableRecords([$inactiveSubscriber])
        ->assertCanNotSeeTableRecords([$activeSubscriber]);
});
