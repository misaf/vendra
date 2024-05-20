<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages\Tenancy;

use App\Models\Tenant\Tenant;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant as TenancyRegisterTenant;

final class RegisterTenant extends TenancyRegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register team';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('domain'),
                TextInput::make('database'),
            ]);
    }

    protected function handleRegistration(array $data): Tenant
    {
        $tenant = Tenant::create($data);

        $tenant->users()->attach(auth()->user());

        return $tenant;
    }
}
