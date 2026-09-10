<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Support\DeliverySchedule;
use Misaf\VendraDeliveryApi\ApiResource\DeliveryScheduleResource;
use Misaf\VendraDeliveryApi\ApiResource\DeliverySlot as DeliverySlotResource;

/**
 * @implements ProviderInterface<DeliveryScheduleResource>
 */
final readonly class DeliveryScheduleProvider implements ProviderInterface
{
    public function __construct(private DeliverySchedule $schedule) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): DeliveryScheduleResource
    {
        $slots = DeliverySlot::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (DeliverySlot $slot): DeliverySlotResource => new DeliverySlotResource(
                id: $slot->id,
                name: self::translations($slot),
                startsAt: $slot->starts_at,
                endsAt: $slot->ends_at,
            ))
            ->all();

        return new DeliveryScheduleResource(
            id: 'current',
            dates: $this->schedule->bookableDates(),
            slots: $slots,
        );
    }

    /**
     * @return array<string, string>
     */
    private static function translations(DeliverySlot $slot): array
    {
        $translations = [];

        foreach ($slot->getTranslations('name') as $locale => $value) {
            if (is_string($locale) && is_string($value)) {
                $translations[$locale] = $value;
            }
        }

        return $translations;
    }
}
