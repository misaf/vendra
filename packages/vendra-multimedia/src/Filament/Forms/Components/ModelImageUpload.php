<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Http\UploadedFile;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

final class ModelImageUpload extends SpatieMediaLibraryFileUpload
{
    public static function getDefaultName(): string
    {
        return 'image';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-multimedia::attributes.image'))
            ->image()
            ->live()
            ->afterStateUpdated(fn (ModelImageUpload $component, Livewire $livewire) => $livewire->validateOnly($component->getStatePath()))
            ->panelLayout('grid')
            ->responsiveImages()
            ->rule(static fn (): Closure => self::storageLimitRule())
            ->helperText(static fn (TenantEntitlements $entitlements): ?string => self::storageFullMessage($entitlements))
            ->columnSpanFull();
    }

    /**
     * Explain up front that the store has no storage left, rather than only on upload.
     *
     * The field stays enabled so existing images can still be removed to free space.
     */
    public static function storageFullMessage(TenantEntitlements $entitlements): ?string
    {
        if ($entitlements->canAdd(PlanLimit::StorageMegabytesPerStore)) {
            return null;
        }

        return EntitlementExceededException::limitReached(
            PlanLimit::StorageMegabytesPerStore,
            $entitlements->limit(PlanLimit::StorageMegabytesPerStore) ?? 0,
        )->getMessage();
    }

    /**
     * Reject a file that would take the store past its plan storage limit.
     *
     * @return Closure(string, mixed, Closure): void
     */
    public static function storageLimitRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value instanceof UploadedFile) {
                return;
            }

            try {
                resolve(TenantEntitlements::class)->assertCanAdd(PlanLimit::StorageMegabytesPerStore, (int) $value->getSize());
            } catch (EntitlementExceededException $exception) {
                $fail($exception->getMessage());
            }
        };
    }
}
