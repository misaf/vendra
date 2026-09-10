<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraNewsletter\Actions\SubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\NewsletterSubscriberResource;

final class CreateNewsletterSubscriber extends CreateRecord
{
    protected static string $resource = NewsletterSubscriberResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-newsletter::navigation.newsletter_subscriber');
    }

    /**
     * @param  array{email: string, name?: string|null}  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return resolve(SubscribeNewsletterSubscriberAction::class)->execute($data);
    }
}
