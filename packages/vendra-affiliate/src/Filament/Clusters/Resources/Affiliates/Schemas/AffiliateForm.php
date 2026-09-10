<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class AffiliateForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            Select::make('user_id')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.user_id'))
                ->columnSpan(['lg' => 1])
                ->label(__('vendra-affiliate::attributes.user'))
                ->live()
                ->preload()
                ->relationship('user', 'username')
                ->required()
                ->searchable()
                ->unique(
                    modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                        ->withoutTrashed(),
                ),

            TextInput::make('code')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.code'))
                ->columnSpan(['lg' => 1])
                ->disabledOn('edit')
                ->helperText(__('vendra-affiliate::attributes.code_helper_text'))
                ->label(__('vendra-affiliate::attributes.code'))
                ->live(onBlur: true)
                ->maxLength(16)
                ->unique(
                    modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                        ->withoutTrashed(),
                ),

            TextInput::make('commission_percent')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.commission_percent'))
                ->columnSpan(['lg' => 1])
                ->default(fn (): int => Config::integer('vendra-affiliate.defaults.commission_percent', 20))
                ->integer()
                ->label(__('vendra-affiliate::attributes.commission_percent'))
                ->live(onBlur: true)
                ->maxValue(100)
                ->minValue(0)
                ->required()
                ->suffix('%'),

            TextInput::make('signup_bounty')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.signup_bounty'))
                ->columnSpan(['lg' => 1])
                ->helperText(__('vendra-affiliate::attributes.signup_bounty_helper_text'))
                ->integer()
                ->label(__('vendra-affiliate::attributes.signup_bounty'))
                ->live(onBlur: true)
                ->minValue(0),

            ToggleButtons::make('status')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.status'))
                ->columnSpanFull()
                ->default(AffiliateStatusEnum::Active)
                ->grouped()
                ->label(__('vendra-affiliate::attributes.status'))
                ->live()
                ->options(AffiliateStatusEnum::class)
                ->required(),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = SpatieTagsInput::make('tags')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.tags'))
                ->columnSpanFull()
                ->label(__('vendra-support::attributes.tags'))
                ->live()
                ->type(Affiliate::TAG_TYPE);
        }

        return $schema
            ->components($components);
    }
}
