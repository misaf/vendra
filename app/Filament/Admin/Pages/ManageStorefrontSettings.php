<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\SelectAction;
use Filament\Clusters\Cluster;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use Misaf\VendraLanguage\Support\TranslationLocales;
use Misaf\VendraStore\Actions\UpdateStorefrontConfigurationAction;
use Misaf\VendraStore\Filament\Schemas\StorefrontConfigurationFields;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;
use Misaf\VendraStore\Support\StorefrontConfigurationValidator;
use Misaf\VendraSupport\Capabilities\CurrencyIntegration;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Filament\Clusters\SystemCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

/**
 * Edit the store's name and description and what its managed storefront publishes.
 *
 * The name and description are {@see GeneralSettings}, one per installed
 * language, and name the admin panel too. The storefront details live in the
 * deployment's configuration, and saving a change there redeploys it.
 *
 * @property-read Schema $form
 */
final class ManageStorefrontSettings extends Page
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = SystemCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?int $navigationSort = NavigationPriority::StorefrontSettings->value;

    protected static ?string $slug = 'storefront';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public ?string $activeLocale = null;

    /**
     * The name and description of every locale but the active one.
     *
     * @var array<string, array{name: ?string, description: ?string}>
     */
    public array $otherLocaleData = [];

    protected ?string $oldActiveLocale = null;

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
        $settings = resolve(GeneralSettings::class);
        $deployment = $this->currentDeployment();
        $deployedNames = $deployment instanceof StorefrontDeployment ? Arr::wrap(Arr::get($deployment->configuration, 'name', [])) : [];
        $storeName = resolve(TenantResolver::class)->current()?->getAttribute('name');
        $locales = $this->getTranslatableLocales();
        $this->activeLocale = in_array(app()->getLocale(), $locales, true) ? app()->getLocale() : Arr::first($locales);

        foreach ($locales as $locale) {
            $this->otherLocaleData[$locale] = $this->localeValues(
                $settings->name[$locale] ?? $deployedNames[$locale] ?? $storeName,
                $settings->description[$locale] ?? null,
            );
        }

        $this->form->fill([
            ...($deployment instanceof StorefrontDeployment ? StorefrontConfigurationMap::toEditableForm($deployment->configuration) : []),
            ...$this->otherLocaleData[$this->activeLocale],
        ]);

        unset($this->otherLocaleData[$this->activeLocale]);
    }

    /**
     * @return list<string>
     */
    public function getTranslatableLocales(): array
    {
        return once(TranslationLocales::active(...));
    }

    public function updatingActiveLocale(): void
    {
        $this->oldActiveLocale = $this->activeLocale;
    }

    /**
     * Keep what was typed for the previous locale and show the next one.
     */
    public function updatedActiveLocale(): void
    {
        if (blank($this->oldActiveLocale) || ! in_array($this->activeLocale, $this->getTranslatableLocales(), true)) {
            $this->activeLocale = $this->oldActiveLocale;

            return;
        }

        $state = $this->data ?? [];
        $this->otherLocaleData[$this->oldActiveLocale] = $this->localeValues(Arr::get($state, 'name', null), Arr::get($state, 'description', null));

        $this->form->fill([
            ...array_diff_key($state, ['name' => true, 'description' => true]),
            ...$this->otherLocaleData[$this->activeLocale],
        ]);

        unset($this->otherLocaleData[$this->activeLocale]);
    }

    public function form(Schema $schema): Schema
    {
        $deployed = $this->currentDeployment() instanceof StorefrontDeployment;

        return $schema
            ->components([
                Tabs::make('storefront')
                    ->tabs([
                        Tab::make(__('setting.general'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('form.name'))
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label(__('form.description'))
                                    ->maxLength(1000)
                                    ->rows(5),
                            ]),
                        ...($deployed ? StorefrontConfigurationFields::editableTabs() : []),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * @return array<Action|SelectAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
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
        $this->ensureEveryLocaleIsNamed();
        $values = [...$this->otherLocaleData, (string) $this->activeLocale => $this->localeValues(Arr::get($data, 'name', null), Arr::get($data, 'description', null))];
        $name = $this->translations($values, 'name');
        $description = $this->translations($values, 'description');
        $deployment = $this->currentDeployment();
        $storefront = [...array_intersect_key($data, array_flip(StorefrontConfigurationMap::EDITABLE_FIELDS)), 'storefront_name' => $name, 'storefront_price_currency' => CurrencyIntegration::defaultCode()];

        if ($deployment instanceof StorefrontDeployment) {
            $this->validateConfiguration(StorefrontConfigurationMap::updateEditable($deployment->configuration, $storefront));
        }

        DB::transaction(function () use ($deployment, $description, $name, $storefront): void {
            resolve(GeneralSettings::class)->fill(['name' => $name, 'description' => $description])->save();

            if ($deployment instanceof StorefrontDeployment) {
                resolve(UpdateStorefrontConfigurationAction::class)->execute($deployment, $storefront);
            }
        });

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

    /**
     * Show the first locale left without a name, since only the active one is on screen.
     *
     * @throws ValidationException
     */
    private function ensureEveryLocaleIsNamed(): void
    {
        foreach ($this->otherLocaleData as $locale => $values) {
            if (Arr::get($values, 'name') !== null) {
                continue;
            }

            $this->updatingActiveLocale();
            $this->activeLocale = $locale;
            $this->updatedActiveLocale();

            throw ValidationException::withMessages([
                'data.name' => __('validation.required', ['attribute' => __('form.name')]),
            ]);
        }
    }

    /**
     * @return array{name: ?string, description: ?string}
     */
    private function localeValues(mixed $name, mixed $description): array
    {
        return [
            'name' => is_string($name) && mb_trim($name) !== '' ? $name : null,
            'description' => is_string($description) && mb_trim($description) !== '' ? $description : null,
        ];
    }

    /**
     * @param  array<string, array{name: ?string, description: ?string}>  $values
     * @param  'name'|'description'  $key
     * @return array<string, string>
     */
    private function translations(array $values, string $key): array
    {
        $translations = [];

        foreach ($this->getTranslatableLocales() as $locale) {
            $value = $values[$locale][$key] ?? null;

            if ($value !== null) {
                $translations[$locale] = $value;
            }
        }

        return $translations;
    }

    private function currentDeployment(): ?StorefrontDeployment
    {
        return once(function (): ?StorefrontDeployment {
            $tenant = resolve(TenantResolver::class)->current();

            return $tenant instanceof Store ? $tenant->storefrontDeployment()->first() : null;
        });
    }
}
