<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraDocument\Enums\DocumentPolicyEnum;
use Misaf\VendraDocument\Models\Document;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\MediaLibrary\HasMedia;

it('applies shared tenant ownership and soft deletes to the document model', function (): void {
    expect(class_uses_recursive(Document::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and((new Document)->getHidden())->toContain('tenant_id');
});

it('keeps jurisdiction-aware document fields fillable and stores files as media', function (): void {
    expect((new Document)->getFillable())->toContain(
        'user_profile_id',
        'type',
        'issuing_country_code',
        'number',
        'metadata',
    )->and(new Document)->toBeInstanceOf(HasMedia::class);
});

it('defines the user profile relationship', function (): void {
    expect(new ReflectionMethod(Document::class, 'userProfile')->getReturnType()?->getName())->toBe(BelongsTo::class);
});

it('defines policy permissions for the document resource', function (): void {
    $permissions = array_column(DocumentPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10)
        ->toHaveSameSize(array_unique($permissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
