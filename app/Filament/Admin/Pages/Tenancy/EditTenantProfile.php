<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile as TenancyEditTenantProfile;

final class EditTenantProfile extends TenancyEditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Team profile';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name'),
        ]);
    }
}
