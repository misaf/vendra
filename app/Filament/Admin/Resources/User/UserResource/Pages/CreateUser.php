<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\User\UserResource\Pages;

use App\Filament\Admin\Resources\User\UserResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['name'] = $data['email'];
        $data['tenant_id'] = 1;

        return $data;
    }
}
