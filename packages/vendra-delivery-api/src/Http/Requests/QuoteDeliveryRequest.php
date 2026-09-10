<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class QuoteDeliveryRequest extends FormRequest
{
    /**
     * Validation rules shared by the HTTP operation and the MCP tool.
     *
     * @var array<string, array<int, string|object>>
     */
    public const array RULES = [
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
        'currencyCode' => ['nullable', 'string', 'size:3'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|object>>
     */
    public function rules(): array
    {
        return self::RULES;
    }
}
