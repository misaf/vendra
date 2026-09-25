<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Models\StorefrontImage;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;

final class StorefrontConfigurationFields
{
    /** @return list<Select|TextInput> */
    public static function creationIdentityFields(bool $optional): array
    {
        return array_values(array_filter(
            self::identityFields($optional),
            fn (Select|TextInput|Hidden $field): bool => ($field instanceof Select || $field instanceof TextInput)
                && in_array($field->getName(), ['storefront_image_id', 'storefront_slug'], true),
        ));
    }

    public static function editable(): Section
    {
        return Section::make(__('vendra-store::attributes.storefront_configuration'))
            ->description(__('vendra-store::attributes.storefront_sample_description'))
            ->schema([
                Grid::make(2)->schema(array_values(array_filter(
                    [...self::identityFields(optional: false), ...self::contactFields(optional: false), ...self::locationAndSocialFields(optional: false)],
                    fn (Select|TextInput|Hidden $field): bool => in_array($field->getName(), StorefrontConfigurationMap::EDITABLE_FIELDS, true),
                ))),
            ])
            ->columnSpanFull();
    }

    /**
     * @return list<Section>
     */
    public static function make(bool $optional): array
    {
        return [
            Section::make(__('vendra-store::attributes.storefront_configuration'))
                ->description(__('vendra-store::attributes.storefront_configuration_description'))
                ->schema([
                    ...($optional ? [
                        self::creationToggle(),
                    ] : []),
                    Grid::make(2)
                        ->schema([
                            ...self::identityFields($optional),
                            ...self::contactFields($optional),
                            ...self::locationAndSocialFields($optional),
                        ])
                        ->visible(fn (Get $get): bool => ! $optional || $get('create_storefront') === true)
                        ->columnSpanFull(),
                ])
                ->visibleOn('create')
                ->columnSpanFull(),
        ];
    }

    public static function creationToggle(bool $default = false): Toggle
    {
        return Toggle::make('create_storefront')
            ->label(__('vendra-store::attributes.create_storefront'))
            ->helperText(__('vendra-store::attributes.create_storefront_hint'))
            ->default($default)
            ->live()
            ->columnSpanFull();
    }

    /**
     * @return list<TextInput|Hidden|Select>
     */
    public static function identityFields(bool $optional): array
    {
        $required = fn (Get $get): bool => ! $optional || $get('create_storefront') === true;

        return [
            Select::make('storefront_image_id')
                ->label(__('vendra-store::navigation.storefront_image'))
                ->helperText(__('vendra-store::attributes.storefront_image_hint'))
                ->options(fn (): array => StorefrontImage::query()->active()->orderBy('image')->pluck('image', 'id')->all())
                ->required($required)
                ->searchable()
                ->preload()
                ->native(false)
                ->live(),
            TextInput::make('storefront_slug')
                ->label(__('vendra-store::attributes.storefront_slug'))
                ->helperText(__('vendra-store::attributes.storefront_slug_hint'))
                ->required($required)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(StorefrontDeployment::class, 'slug')
                ->extraAttributes(['dir' => 'ltr'])
                ->placeholder('rose-garden')
                ->maxLength(100),
            TextInput::make('storefront_name_en')
                ->label(__('vendra-store::attributes.storefront_name_en'))
                ->required($required)
                ->maxLength(255),
            TextInput::make('storefront_name_fa')
                ->label(__('vendra-store::attributes.storefront_name_fa'))
                ->required($required)
                ->maxLength(255),
            Hidden::make('storefront_business_type')
                ->default('Florist')
                ->required($required)
                ->dehydrated(),
            TextInput::make('storefront_price_currency')
                ->label(__('vendra-store::attributes.storefront_price_currency'))
                ->default('IRR')
                ->required($required)
                ->length(3),
            TextInput::make('storefront_og_image')
                ->label(__('vendra-store::attributes.storefront_og_image'))
                ->helperText(__('vendra-store::attributes.storefront_og_image_hint'))
                ->maxLength(2048),
        ];
    }

    /**
     * @return list<TextInput>
     */
    public static function contactFields(bool $optional): array
    {
        $required = fn (Get $get): bool => ! $optional || $get('create_storefront') === true;

        return [
            TextInput::make('storefront_mobile_phone')
                ->label(__('vendra-store::attributes.storefront_mobile_phone'))
                ->required($required)
                ->tel()
                ->extraAttributes(['dir' => 'ltr'])
                ->placeholder('09121234567')
                ->maxLength(50),
            TextInput::make('storefront_office_phone')
                ->label(__('vendra-store::attributes.storefront_office_phone'))
                ->required($required)
                ->tel()
                ->extraAttributes(['dir' => 'ltr'])
                ->placeholder('02112345678')
                ->maxLength(50),
            TextInput::make('storefront_contact_email')
                ->label(__('vendra-store::attributes.storefront_contact_email'))
                ->required($required)
                ->email()
                ->autocomplete('email')
                ->extraAttributes(['dir' => 'ltr'])
                ->placeholder('contact@example.com')
                ->maxLength(255),
            TextInput::make('storefront_hours_open')
                ->label(__('vendra-store::attributes.storefront_hours_open'))
                ->placeholder('08:00')
                ->extraAttributes(['dir' => 'ltr'])
                ->required($required)
                ->regex('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/'),
            TextInput::make('storefront_hours_close')
                ->label(__('vendra-store::attributes.storefront_hours_close'))
                ->placeholder('21:00')
                ->extraAttributes(['dir' => 'ltr'])
                ->required($required)
                ->regex('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/'),
        ];
    }

    /**
     * @return list<TextInput>
     */
    public static function locationAndSocialFields(bool $optional): array
    {
        $required = fn (Get $get): bool => ! $optional || $get('create_storefront') === true;

        return [
            TextInput::make('storefront_locality')
                ->label(__('vendra-store::attributes.storefront_locality'))
                ->required($required)
                ->maxLength(255),
            TextInput::make('storefront_country')
                ->label(__('vendra-store::attributes.storefront_country'))
                ->default('IR')
                ->extraAttributes(['dir' => 'ltr'])
                ->required($required)
                ->length(2),
            TextInput::make('storefront_map_query')
                ->label(__('vendra-store::attributes.storefront_map_query'))
                ->required($required)
                ->placeholder('35.6892, 51.3890')
                ->maxLength(500),
            TextInput::make('storefront_whatsapp_phone')
                ->label(__('vendra-store::attributes.storefront_whatsapp_phone'))
                ->required($required)
                ->tel()
                ->extraAttributes(['dir' => 'ltr'])
                ->placeholder('+989121234567')
                ->maxLength(50),
            TextInput::make('storefront_telegram_username')
                ->label(__('vendra-store::attributes.storefront_telegram_username'))
                ->required($required)
                ->prefix('@')
                ->extraAttributes(['dir' => 'ltr'])
                ->maxLength(100),
            TextInput::make('storefront_instagram_username')
                ->label(__('vendra-store::attributes.storefront_instagram_username'))
                ->required($required)
                ->prefix('@')
                ->extraAttributes(['dir' => 'ltr'])
                ->maxLength(100),
        ];
    }
}
