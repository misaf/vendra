<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use Filament\Actions\Action;
use Filament\Clusters\Cluster;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
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

/**
 * Edit the details the store's managed storefront publishes.
 *
 * The details live in the deployment's configuration rather than in a settings
 * class, and saving them redeploys the storefront.
 *
 * @property-read Schema $form
 */
final class ManageStorefrontSettings extends Page
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = SystemCluster::class;

    protected static ?int $navigationSort = NavigationPriority::StorefrontSettings->value;

    protected static ?string $slug = 'storefront';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return parent::canAccess() && self::currentDeployment() instanceof StorefrontDeployment;
    }

    public static function getNavigationLabel(): string
    {
        return __('page.storefront');
    }

    public function getTitle(): string
    {
        return __('page.storefront');
    }

    public function mount(): void
    {
        $this->form->fill(StorefrontConfigurationMap::toEditableForm($this->deployment()->configuration));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                StorefrontConfigurationFields::editable(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('filament-spatie-laravel-settings-plugin::pages/settings-page.form.actions.save.label'))
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    /**
     * @throws ValidationException
     */
    public function save(): void
    {
        $data = $this->form->getState();
        $deployment = $this->deployment();

        $this->validateConfiguration(StorefrontConfigurationMap::updateEditable($deployment->configuration, $data));

        resolve(UpdateStorefrontConfigurationAction::class)->execute($deployment, $data);

        Notification::make()
            ->success()
            ->title(__('filament-spatie-laravel-settings-plugin::pages/settings-page.notifications.saved.title'))
            ->send();
    }

    /**
     * Report configuration errors on the form fields that feed them.
     *
     * @param  array<string, mixed>  $configuration
     *
     * @throws ValidationException
     */
    private function validateConfiguration(array $configuration): void
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

    private function deployment(): StorefrontDeployment
    {
        return once(function (): StorefrontDeployment {
            $deployment = self::currentDeployment();

            abort_unless($deployment instanceof StorefrontDeployment, 404);

            return $deployment;
        });
    }

    private static function currentDeployment(): ?StorefrontDeployment
    {
        $tenant = resolve(TenantResolver::class)->current();

        return $tenant instanceof Store ? $tenant->storefrontDeployment()->first() : null;
    }
}
