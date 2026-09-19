<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Actions;

use Misaf\VendraInquiry\Models\Inquiry;

final class SubmitInquiryAction
{
    /**
     * The message is stored verbatim. The caller validates the fields first.
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
        ]);
    }
}
