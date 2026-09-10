<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Context;

use Misaf\VendraSupport\Context\RequestJobContext;

/**
 * Newsletter-owned observability context keys passed through
 * {@see RequestJobContext::$metadata}.
 */
final class NewsletterContextKeys
{
    public const string NEWSLETTER_ID = 'newsletter_id';

    public const string SUBSCRIBER_ID = 'subscriber_id';
}
