<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * The image crash-loops on an incomplete configuration, so this fails early
 * with the missing field. {@see deploymentRules()} shares the same field lists.
 */
final class StorefrontConfigurationValidator
{
    private const string SLUG = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private const string DOMAIN = '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/i';

    /**
     * The fields derived from the deployment row rather than the form.
     *
     * @var list<string>
     */
    private const array IDENTITY = ['slug', 'domain', 'siteUrl'];

    /**
     * The fields the image requires at boot, mirroring its schema.
     *
     * `ogImage` is optional there and sent as an empty string, so it is not required.
     *
     * @var list<string>
     */
    private const array REQUIRED_STRINGS = ['slug', 'domain', 'siteUrl', 'businessType', 'priceCurrency'];

    /** @var array<string, list<string>> */
    private const array REQUIRED_OBJECTS = [
        'address' => ['locality', 'country'],
        'contact' => ['mobilePhone', 'officePhone', 'email', 'hoursOpen', 'hoursClose', 'mapQuery'],
        'name' => [],
        'social' => ['whatsappPhone', 'telegramUsername', 'instagramUsername'],
    ];

    /**
     * @return array<string, string>
     */
    public static function deploymentRules(): array
    {
        $rules = [];

        foreach (self::REQUIRED_STRINGS as $key) {
            if (! in_array($key, self::IDENTITY, true)) {
                $rules[$key] = 'required|string';
            }
        }

        foreach (self::REQUIRED_OBJECTS as $key => $fields) {
            $rules[$key] = 'required|array';

            foreach ($fields as $field) {
                $rules[$key.'.'.$field] = 'required|string';
            }
        }

        return $rules;
    }

    /** @throws InvalidArgumentException */
    public function validate(StorefrontProvisionRequest $request): void
    {
        throw_if(preg_match(self::SLUG, $request->slug) !== 1, InvalidArgumentException::class, 'The storefront slug must contain lowercase letters, digits, and hyphens.');

        throw_if(preg_match(self::DOMAIN, $request->domain) !== 1, InvalidArgumentException::class, 'The storefront domain is invalid.');

        throw_if(mb_trim($request->image) === '', InvalidArgumentException::class, 'A storefront image is required.');

        $configuration = $request->configuration;

        throw_if((Arr::get($configuration, 'slug', null)) !== $request->slug || (Arr::get($configuration, 'domain', null)) !== $request->domain, InvalidArgumentException::class, 'The storefront configuration identity does not match the deployment.');

        $missing = $this->missingFields($configuration);

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'The storefront configuration is missing required fields: '.implode(', ', $missing).'.',
            );
        }
    }

    /**
     * @param  array<array-key, mixed>  $configuration
     * @return list<string>
     */
    private function missingFields(array $configuration): array
    {
        $missing = [];

        foreach (self::REQUIRED_STRINGS as $key) {
            $value = $configuration[$key] ?? null;

            if (! is_string($value) || mb_trim($value) === '') {
                $missing[] = $key;
            }
        }

        foreach (self::REQUIRED_OBJECTS as $key => $fields) {
            $nested = $configuration[$key] ?? null;

            if (! is_array($nested) || $nested === []) {
                $missing[] = $key;

                continue;
            }

            foreach ($fields as $field) {
                $value = $nested[$field] ?? null;

                if (! is_string($value) || mb_trim($value) === '') {
                    $missing[] = $key.'.'.$field;
                }
            }
        }

        return $missing;
    }
}
