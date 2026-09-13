<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Actions;

use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraInquiry\Models\Inquiry;

final class SubmitInquiryAction
{
    /**
     * Record what someone wrote in from the storefront.
     *
     * The message is stored verbatim — it is evidence of what a customer
     * asked for, so nothing here trims, formats, or interprets it. The caller
     * validates the fields first — the storefront API does so with
     * `SubmitInquiryRequest`.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function execute(
        string $name,
        string $email,
        string $message,
        ?string $phone = null,
        ?string $occasion = null,
        ?string $source = null,
        ?string $locale = null,
        ?array $metadata = null,
    ): Inquiry {
        $attributes = [
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'phone' => $phone,
            'occasion' => $occasion,
            'source' => $source,
            'locale' => $locale,
        ];

        return Inquiry::query()->create([
            ...$attributes,
            'metadata' => $metadata,
            'status' => InquiryStatusEnum::New,
        ]);
    }
}
