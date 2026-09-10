<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Actions;

use Illuminate\Support\Facades\Validator;
use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraInquiry\Models\Inquiry;

final class SubmitInquiryAction
{
    /**
     * Record what someone wrote in from the storefront.
     *
     * The message is stored verbatim — it is evidence of what a customer
     * asked for, so nothing here trims, formats, or interprets it. Validation
     * lives with the operation rather than the caller so an enquiry arriving
     * over HTTP, from a console command, or from a test is held to the same
     * shape.
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

        Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:64'],
            'occasion' => ['nullable', 'string', 'max:64'],
            'source' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:35'],
        ])->validate();

        return Inquiry::query()->create([
            ...$attributes,
            'metadata' => $metadata,
            'status' => InquiryStatusEnum::New,
        ]);
    }
}
