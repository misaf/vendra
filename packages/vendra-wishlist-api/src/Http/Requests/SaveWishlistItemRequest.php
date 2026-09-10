<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SaveWishlistItemRequest extends FormRequest
{
    /**
     * Validation rules shared by the HTTP operation and the MCP tool.
     *
     * @var array<string, array<int, string>>
     */
    public const array RULES = [
        'sellableType' => ['required', 'string', 'max:64'],
        'sellableId' => ['required', 'integer', 'min:1'],
        'metadata' => ['nullable', 'array'],
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
