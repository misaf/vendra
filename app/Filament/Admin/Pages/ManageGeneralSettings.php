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
use Illuminate\Validation\ValidationException;
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

    /**
     * Save the settings and the storefront configuration together or not at all.
     */
    protected ?bool $hasDatabaseTransactions = true;

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
            $this->validateStorefrontConfiguration(StorefrontConfigurationMap::updateEditable($deployment->configuration, $storefrontData));
            resolve(UpdateStorefrontConfigurationAction::class)->execute($deployment, $storefrontData);
        }

        return $data;
    }

    /**
     * Report configuration errors on the form fields that feed them.
     *
     * @param  array<string, mixed>  $configuration
     *
     * @throws ValidationException
     */
    private function validateStorefrontConfiguration(array $configuration): void
    {
        $validator = Validator::make($configuration, StorefrontConfigurationValidator::deploymentRules());

        if ($validator->passes()) {
            return;
        }

        $fieldsByPath = array_flip(StorefrontConfigurationMap::FIELDS);
        $messages = [];

        foreach ($validator->errors()->messages() as $path => $pathMessages) {
            $key = array_key_exists($path, $fieldsByPath) ? 'data.'.$fieldsByPath[$path] : $path;
            $messages[$key] = $pathMessages;
        }

        throw ValidationException::withMessages($messages);
    }

    private function deployment(): ?StorefrontDeployment
    {
        return once(function (): ?StorefrontDeployment {
            $tenant = resolve(TenantResolver::class)->current();

            return $tenant instanceof Store ? $tenant->storefrontDeployment()->first() : null;
        });
    }

    public function getTitle(): string
    {
        return __('page.configuration');
    }
}
