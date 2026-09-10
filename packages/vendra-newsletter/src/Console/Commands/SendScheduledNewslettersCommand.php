<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Misaf\VendraNewsletter\Actions\SendNewsletterAction;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Contracts\TenantResolver;

#[Description('Dispatch newsletters whose scheduled send time has passed')]
#[Signature('vendra-newsletter:send-scheduled')]
final class SendScheduledNewslettersCommand extends Command
{
    /**
     * Dispatch due newsletters for every tenant. The support layer runs the
     * closure once per tenant (or once globally when no tenant provider is
     * installed), so subscriber scoping stays correct without this module
     * knowing anything about the concrete tenant.
     */
    public function handle(SendNewsletterAction $sendNewsletter, TenantResolver $tenants): int
    {
        new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'scheduled_newsletters',
        )->scope(function () use ($sendNewsletter, $tenants): void {
            $tenants->eachTenant(function () use ($sendNewsletter): void {
                Newsletter::query()
                    ->due()
                    ->orderBy('scheduled_at')
                    ->get()
                    ->each(function (Newsletter $newsletter) use ($sendNewsletter): void {
                        $recipients = $sendNewsletter->execute($newsletter);

                        $this->components->info(sprintf(
                            'Queued newsletter #%d for %d subscriber(s).',
                            $newsletter->id,
                            $recipients,
                        ));
                    });
            });
        });

        return self::SUCCESS;
    }
}
