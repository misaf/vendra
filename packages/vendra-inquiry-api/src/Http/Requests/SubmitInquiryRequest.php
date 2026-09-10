<?php

declare(strict_types=1);

namespace Misaf\VendraInquiryApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitInquiryRequest extends FormRequest
{
    /**
     * Validation rules shared by the HTTP operation and the MCP tool.
     *
     * These mirror `SubmitInquiryAction`: the action validates the operation
     * wherever it is called from, and these give the storefront the same
     * answer before a row is ever attempted.
     *
     * @var array<string, array<int, string>>
     */
    public const array RULES = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'message' => ['required', 'string', 'max:5000'],
        'phone' => ['nullable', 'string', 'max:64'],
        'occasion' => ['nullable', 'string', 'max:64'],
        'preferredLocale' => ['nullable', 'string', 'max:35'],
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
