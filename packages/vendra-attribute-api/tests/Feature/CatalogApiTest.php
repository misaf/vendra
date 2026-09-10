<?php

declare(strict_types=1);

use Misaf\VendraAttribute\Database\Factories\AttributeFactory;
use Misaf\VendraAttribute\Database\Factories\AttributeValueFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('filters catalog attributes and embeds option references', function (): void {
    $attribute = AttributeFactory::new()->active()->create(['name' => 'Material']);
    $option = AttributeValueFactory::new()->forAttribute($attribute)->create([
        'value' => 'Cotton',
        'attributable_type' => 'catalog-test',
        'attributable_id' => 1,
    ]);
    AttributeFactory::new()->active()->create(['name' => 'Colour']);

    $this->getJson('/api/catalog/attributes?search=Mater', ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 1)
        ->assertJsonPath('member.0.id', $attribute->id)
        ->assertJsonPath('member.0.values.0.id', $option->id);

    $this->getJson("/api/catalog/attribute-values?attributeId={$attribute->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 1)
        ->assertJsonPath('member.0.attribute.id', $attribute->id);
});

it('isolates catalog resources by tenant', function (): void {
    $visible = AttributeFactory::new()->active()->create();
    $otherTenant = createTestTenant();
    switchToTestTenant($otherTenant);
    $hidden = AttributeFactory::new()->active()->create();
    switchToTestTenant($visible->tenant);

    $response = $this->getJson('/api/catalog/attributes', ['Accept' => 'application/ld+json']);

    $response->assertOk()->assertJsonFragment(['id' => $visible->id]);
    expect(collect($response->json('member'))->pluck('id'))->not->toContain($hidden->id);
});
