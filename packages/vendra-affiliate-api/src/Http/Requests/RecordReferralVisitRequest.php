<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliateApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RecordReferralVisitRequest extends FormRequest
{
    /**
     * Validation rules shared by the HTTP operation and the MCP tool.
     *
     * @var array<string, array<int, string>>
     */
    public const array RULES = [
        'code' => ['required', 'string', 'max:64'],
        'landingUrl' => ['nullable', 'url:http,https', 'max:255'],
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
