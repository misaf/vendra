<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraInquiry\Enums\InquiryPolicyEnum;
use Misaf\VendraInquiry\Models\Inquiry;
use Misaf\VendraInquiry\Policies\InquiryPolicy;

it('authorizes inquiry abilities through permissions', function (string $method, InquiryPolicyEnum $permission): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->once()->with($permission->value)->andReturnTrue();

    $arguments = in_array($method, ['view', 'update', 'delete'], true)
        ? [$user, new Inquiry]
        : [$user];

    expect((new InquiryPolicy)->{$method}(...$arguments))->toBeTrue();
})->with([
    ['view', InquiryPolicyEnum::View],
    ['viewAny', InquiryPolicyEnum::ViewAny],
    ['update', InquiryPolicyEnum::Update],
    ['delete', InquiryPolicyEnum::Delete],
    ['deleteAny', InquiryPolicyEnum::DeleteAny],
]);

it('never creates an enquiry from administration', function (): void {
    expect((new InquiryPolicy)->create(Mockery::mock(Authorizable::class)))->toBeFalse();
});
