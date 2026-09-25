<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Settings\GeneralSettings;
use Filament\Clusters\Cluster;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraStore\Actions\UpdateStorefrontConfigurationAction;
use Misaf\VendraStore\Filament\Schemas\StorefrontConfigurationFields;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;
use Misaf\VendraStore\Support\StorefrontConfigurationValidator;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Filament\Clusters\SystemCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class ManageGeneralSettings extends SettingsPage
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = SystemCluster::class;

    protected static ?int $navigationSort = NavigationPriority::GeneralSettings->value;

    protected static string $settings = GeneralSettings::class;

    protected static ?string $slug = 'configurations';

    public static function getModelLabel(): string
    {
        return __('page.configuration');
    }

    public static function getNavigationLabel(): string
    {
        return __('page.configuration');
    }

    public static function getPluralModelLabel(): string
    {
        return __('page.configuration');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('setting.general'))
                    ->schema([
                        TextInput::make('site_title')
                            ->columnSpanFull()
                            ->label(__('form.title'))
                            ->maxLength(255)
                            ->required(),

                        Textarea::make('site_description')
                            ->columnSpanFull()
                            ->label(__('form.description'))
                            ->maxLength(1000)
                            ->rows(5),
                    ])
                    ->columnSpanFull(),

                StorefrontConfigurationFields::editable()
                    ->visible(fn (): bool => $this->deployment() instanceof StorefrontDeployment),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $deployment = $this->deployment();

        return $deployment instanceof StorefrontDeployment
            ? [...$data, ...StorefrontConfigurationMap::toEditableForm($deployment->configuration)]
            : $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $storefrontData = [];

        foreach (StorefrontConfigurationMap::EDITABLE_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $storefrontData[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        $deployment = $this->deployment();

        if ($deployment instanceof StorefrontDeployment && $storefrontData !== []) {
            $configuration = StorefrontConfigurationMap::updateEditable($deployment->configuration, $storefrontData);
            Validator::make($configuration, StorefrontConfigurationValidator::deploymentRules())->validate();
            resolve(UpdateStorefrontConfigurationAction::class)->execute($deployment, $storefrontData);
        }

        return $data;
    }

    private function deployment(): ?StorefrontDeployment
    {
        $tenant = resolve(TenantResolver::class)->current();

        return $tenant instanceof Store ? $tenant->storefrontDeployment()->first() : null;
    }

    public function getTitle(): string
    {
        return __('page.configuration');
    }
}
