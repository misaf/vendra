<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\ManageStorefrontSettings;
use App\Settings\GeneralSettings;
use Filament\Facades\Filament;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraPermission\Actions\CreateRoleAction;
use Misaf\VendraStore\Actions\RequestStorefrontDeploymentAction;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontConfigurationMap;
use Misaf\VendraUser\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

function actAsStoreAdministrator(Store $store): void
{
    $store->makeCurrent();
    $role = resolve(CreateRoleAction::class)->execute($store, Config::string('vendra-permission.admin_role'), guardName: 'web');
    $user = User::factory()->forTenant($store)->create();
    $user->assignRole($role);
    actingAs($user, 'web');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
}

it('lets the store administrator replace sample details and request a redeployment', function (): void {
    Config::set('container.drivers.docker.host', 'http://provisioner:8080');
    $store = Store::factory()->active()->create();
    $form = storefrontRequestData();
    Queue::fake();
    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute(
        $store,
        'acme.test',
        Arr::only($form, ['storefront_image_id', 'storefront_slug']),
    );
    expect(Arr::get($deployment->configuration, 'contact.mobilePhone'))->toBe('00000000000');
    Queue::assertPushed(ProvisionStorefrontJob::class, fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && ! $job->force);
    Queue::fake();
    actAsStoreAdministrator($store);

    livewire(ManageStorefrontSettings::class)
        ->assertFormFieldExists('storefront_mobile_phone')
        ->assertFormFieldDoesNotExist('storefront_slug')
        ->fillForm(Arr::only($form, StorefrontConfigurationMap::EDITABLE_FIELDS))
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Arr::get($deployment->fresh()->configuration, 'contact.mobilePhone'))->toBe('09120000000')
        ->and(Arr::get($deployment->fresh()->configuration, 'address.locality'))->toBe('Tehran')
        ->and($deployment->fresh()->slug)->toBe('acme-flowers');
    Queue::assertPushed(ProvisionStorefrontJob::class, fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force);
});

it('lets the store administrator update contact details without changing another store', function (): void {
    Config::set('container.drivers.docker.host', 'http://provisioner:8080');
    $store = Store::factory()->active()->create();
    $form = storefrontRequestData();
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'configuration' => StorefrontConfigurationMap::toConfiguration($form),
    ]);
    $otherStore = Store::factory()->active()->create();
    $otherDeployment = StorefrontDeployment::factory()->for($otherStore)->create([
        'configuration' => StorefrontConfigurationMap::toConfiguration($form),
    ]);
    actAsStoreAdministrator($store);
    Queue::fake();

    livewire(ManageStorefrontSettings::class)
        ->assertFormSet(['storefront_office_phone' => '02100000000'])
        ->assertFormSet(['name' => 'Acme Flowers'])
        ->fillForm(['storefront_office_phone' => '02199999999', 'name' => 'Rose Garden', 'description' => 'Fresh flowers daily.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Arr::get($deployment->fresh()->configuration, 'contact.officePhone'))->toBe('02199999999')
        ->and(Arr::get($deployment->fresh()->configuration, 'name'))->toBe(['en' => 'Rose Garden', 'fa' => 'گل‌فروشی اکمی'])
        ->and(resolve(GeneralSettings::class)->name)->toBe(['en' => 'Rose Garden'])
        ->and(resolve(GeneralSettings::class)->description)->toBe(['en' => 'Fresh flowers daily.'])
        ->and(Arr::get($otherDeployment->fresh()->configuration, 'contact.officePhone'))->toBe('02100000000')
        ->and(Arr::get($otherDeployment->fresh()->configuration, 'name.en'))->toBe('Acme Flowers');
    Queue::assertPushed(ProvisionStorefrontJob::class, fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force);
});

it('rejects an empty phone without changing storefront configuration', function (): void {
    $store = Store::factory()->active()->create();
    $form = storefrontRequestData();
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'configuration' => StorefrontConfigurationMap::toConfiguration($form),
    ]);
    actAsStoreAdministrator($store);
    Queue::fake();

    livewire(ManageStorefrontSettings::class)
        ->fillForm(['storefront_mobile_phone' => ''])
        ->call('save')
        ->assertHasFormErrors(['storefront_mobile_phone' => 'required']);

    expect(Arr::get($deployment->fresh()->configuration, 'contact.mobilePhone'))->toBe('09120000000');
    Queue::assertNotPushed(ProvisionStorefrontJob::class);
});

it('lets a store without a managed storefront name itself and the admin panel', function (): void {
    $store = Store::factory()->active()->create(['name' => 'Local Flowers']);
    actAsStoreAdministrator($store);

    livewire(ManageStorefrontSettings::class)
        ->assertFormSet(['name' => 'Local Flowers'])
        ->assertFormFieldDoesNotExist('storefront_mobile_phone')
        ->fillForm(['name' => 'Rose Garden'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(resolve(GeneralSettings::class)->name)->toBe(['en' => 'Rose Garden'])
        ->and(Filament::getPanel('admin')->getBrandName())->toBe('Rose Garden')
        ->and($store->storefrontDeployment()->exists())->toBeFalse();
});

it('switches the name and description by installed language', function (): void {
    $store = Store::factory()->active()->create(['name' => 'Local Flowers']);
    actAsStoreAdministrator($store);
    Language::factory()->active()->create(['locale' => 'en']);
    Language::factory()->active()->create(['locale' => 'fa']);

    livewire(ManageStorefrontSettings::class)
        ->set('activeLocale', 'en')
        ->fillForm(['name' => 'Rose Garden', 'description' => 'Fresh flowers daily.'])
        ->set('activeLocale', 'fa')
        ->assertFormSet(['name' => 'Local Flowers', 'description' => null])
        ->fillForm(['name' => 'گل رز'])
        ->set('activeLocale', 'en')
        ->assertFormSet(['name' => 'Rose Garden', 'description' => 'Fresh flowers daily.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(resolve(GeneralSettings::class)->name)->toBe(['en' => 'Rose Garden', 'fa' => 'گل رز'])
        ->and(resolve(GeneralSettings::class)->description)->toBe(['en' => 'Fresh flowers daily.']);
});

it('requires a name for every installed language', function (): void {
    actAsStoreAdministrator(Store::factory()->active()->create());
    Language::factory()->active()->create(['locale' => 'en']);
    Language::factory()->active()->create(['locale' => 'fa']);

    livewire(ManageStorefrontSettings::class)
        ->fillForm(['name' => ''])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);

    livewire(ManageStorefrontSettings::class)
        ->set('activeLocale', 'fa')
        ->fillForm(['name' => ''])
        ->set('activeLocale', 'en')
        ->call('save')
        ->assertSet('activeLocale', 'fa')
        ->assertHasFormErrors(['name']);

    expect(resolve(GeneralSettings::class)->name)->toBeEmpty();
});
