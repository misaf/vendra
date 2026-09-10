<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PlaceOrderRequest extends FormRequest
{
    /**
     * Validation rules shared by the HTTP operation and the MCP tool.
     *
     * @var array<string, array<int, string>>
     */
    public const array RULES = [
        'cartToken' => ['required', 'string', 'max:64'],
        'currencyCode' => ['nullable', 'string', 'size:3'],
        'gateway' => ['nullable', 'string', 'max:64'],
        'paymentReference' => ['nullable', 'string', 'max:255'],
        'cardMessage' => ['nullable', 'string', 'max:1000'],
        'recipientName' => ['nullable', 'string', 'max:255'],
        'addressId' => ['nullable', 'integer', 'min:1'],
        'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
        'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        'deliveryDate' => ['nullable', 'date_format:Y-m-d'],
        'deliverySlotId' => ['nullable', 'integer', 'min:1'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return self::RULES;
    }
}
