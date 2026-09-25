<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Settings;

use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Settings;

final class InquirySettings extends Settings implements ShouldLogActivity
{
    /**
     * The occasion slugs a storefront inquiry may name.
     *
     * @var list<string>
     */
    public array $occasions;

    public static function group(): string
    {
        return 'inquiry';
    }
}
