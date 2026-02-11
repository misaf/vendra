<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

final class DefaultSignatureValidator implements SignatureValidator
{
    /**
     * @param Request $request
     * @param WebhookConfig $config
     * @return bool
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return true;
    }
}
