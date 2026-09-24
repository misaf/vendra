<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Arr;

/**
 * A test asserts every mapped field still exists in the Filament schema.
 */
final class StorefrontConfigurationMap
{
    /**
     * @var array<string, string>
     */
    public const array FIELDS = [
        'storefront_name_en' => 'name.en',
        'storefront_name_fa' => 'name.fa',
        'storefront_business_type' => 'businessType',
        'storefront_price_currency' => 'priceCurrency',
        'storefront_og_image' => 'ogImage',
        'storefront_locality' => 'address.locality',
        'storefront_country' => 'address.country',
        'storefront_mobile_phone' => 'contact.mobilePhone',
        'storefront_office_phone' => 'contact.officePhone',
        'storefront_contact_email' => 'contact.email',
        'storefront_hours_open' => 'contact.hoursOpen',
        'storefront_hours_close' => 'contact.hoursClose',
        'storefront_map_query' => 'contact.mapQuery',
        'storefront_whatsapp_phone' => 'social.whatsappPhone',
        'storefront_telegram_username' => 'social.telegramUsername',
        'storefront_instagram_username' => 'social.instagramUsername',
    ];

    /**
     * @var list<string>
     */
    private const array UPPERCASED = ['storefront_price_currency', 'storefront_country'];

    /**
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    public static function toConfiguration(array $form): array
    {
        $configuration = [];

        foreach (self::FIELDS as $field => $path) {
            Arr::set($configuration, $path, self::value($form, $field));
        }

        // Per-locale copy overrides are the only way a store customizes its wording.
        $messages = self::messages($form);

        if ($messages !== []) {
            $configuration['messages'] = $messages;
        }

        return array_filter($configuration, is_string(...), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param  array<string, mixed>  $form
     */
    private static function value(array $form, string $field): string
    {
        $value = $form[$field] ?? null;
        $value = is_scalar($value) ? (string) $value : '';

        return in_array($field, self::UPPERCASED, true) ? mb_strtoupper($value) : $value;
    }

    /**
     * Malformed entries are dropped, since the storefront refuses to boot on a
     * configuration that does not parse.
     *
     * @param  array<string, mixed>  $form
     * @return array<string, array<string, mixed>>
     */
    private static function messages(array $form): array
    {
        $value = Arr::get($form, 'storefront_messages', null);

        if (! is_array($value)) {
            return [];
        }

        $messages = [];

        foreach ($value as $locale => $overrides) {
            if (! is_string($locale) || $locale === '' || ! is_array($overrides)) {
                continue;
            }

            $overrides = array_filter($overrides, is_string(...), ARRAY_FILTER_USE_KEY);

            if ($overrides !== []) {
                $messages[$locale] = $overrides;
            }
        }

        return $messages;
    }
}
