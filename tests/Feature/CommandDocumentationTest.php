<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

it('describes module seed commands with the correct domain', function (
    string $commandName,
    string $description,
    string $tenantDescription,
): void {
    $command = Artisan::all()[$commandName];

    expect($command->getDescription())->toBe($description)
        ->and($command->getDefinition()->getArgument('tenant')->getDescription())
        ->toBe($tenantDescription);
})->with([
    'custom page' => [
        'vendra-custom-page:seed',
        'Seed custom page module data for a tenant',
        'Tenant ID or slug to seed custom page data for',
    ],
    'FAQ' => [
        'vendra-faq:seed',
        'Seed FAQ module data for a tenant',
        'Tenant ID or slug to seed FAQ data for',
    ],
    'permission' => [
        'vendra-permission:seed',
        'Seed permission module data for a tenant',
        'Tenant ID or slug to seed permission data for',
    ],
    'user' => [
        'vendra-user:seed',
        'Seed user module data for a tenant',
        'Tenant ID or slug to seed user data for',
    ],
]);
