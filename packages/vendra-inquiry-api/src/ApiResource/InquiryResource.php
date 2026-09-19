<?php

declare(strict_types=1);

namespace Misaf\VendraInquiryApi\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\Post;
use Misaf\VendraInquiryApi\Http\Requests\SubmitInquiryRequest;
use Misaf\VendraInquiryApi\State\SubmitInquiryProcessor;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * The storefront contact form: unauthenticated, throttled, and answered with `204`.
 */
#[ApiResource(
    shortName: 'Inquiry',
    operations: [
        new Post(
            uriTemplate: '/support/inquiries',
            status: 204,
            output: false,
            processor: SubmitInquiryProcessor::class,
            rules: SubmitInquiryRequest::class,
            middleware: 'throttle:10,1',
        ),
    ],
    mcp: [
        'submit_inquiry' => new McpTool(
            description: 'Send a contact enquiry to the studio.',
            input: self::class,
            processor: SubmitInquiryProcessor::class,
            validate: true,
            rules: SubmitInquiryRequest::RULES,
        ),
    ],
)]
final class InquiryResource
{
    public string $name = '';

    public string $email = '';

    public string $message = '';

    public ?string $phone = null;

    public ?string $occasion = null;

    /**
     * Keep multi-word inputs camelCase on the wire despite the snake_case name converter.
     */
    #[SerializedName('preferredLocale')]
    public ?string $preferredLocale = null;
}
