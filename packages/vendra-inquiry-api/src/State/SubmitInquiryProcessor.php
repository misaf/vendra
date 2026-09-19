<?php

declare(strict_types=1);

namespace Misaf\VendraInquiryApi\State;

use ApiPlatform\Laravel\ApiResource\ValidationError;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Misaf\VendraInquiry\Actions\SubmitInquiryAction;
use Misaf\VendraInquiryApi\ApiResource\InquiryResource;

/**
 * The source is taken from the request, not the body, so it cannot be spoofed.
 *
 * @implements ProcessorInterface<InquiryResource, void>
 */
final readonly class SubmitInquiryProcessor implements ProcessorInterface
{
    public function __construct(
        private SubmitInquiryAction $submitInquiry,
        private Request $request,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $this->ensureConfiguredOccasion($data->occasion);

        $this->submitInquiry->execute(
            name: $data->name,
            email: $data->email,
            message: $data->message,
            phone: $data->phone,
            occasion: $data->occasion,
            source: 'storefront',
            locale: $data->preferredLocale ?? $this->request->getPreferredLanguage(),
        );
    }

    /**
     * Reject an occasion outside the configured list, which a constant rule cannot check.
     */
    private function ensureConfiguredOccasion(?string $occasion): void
    {
        if ($occasion === null || in_array($occasion, Config::array('vendra-inquiry.occasions', []), true)) {
            return;
        }

        $message = __('validation.in', ['attribute' => 'occasion']);

        throw new ValidationError(
            message: $message,
            code: 'occasion',
            violations: [['propertyPath' => 'occasion', 'message' => $message]],
        );
    }
}
