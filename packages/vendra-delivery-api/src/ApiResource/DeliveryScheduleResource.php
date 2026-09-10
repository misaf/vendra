<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\McpTool;
use Misaf\VendraDeliveryApi\State\DeliveryScheduleProvider;

/**
 * Everything a checkout needs to offer a date and a window in one call: the
 * dates still bookable after the same-day cutoff, and the windows the studio
 * delivers in.
 */
#[ApiResource(
    shortName: 'DeliverySchedule',
    operations: [
        new Get(
            uriTemplate: '/delivery/schedule',
            provider: DeliveryScheduleProvider::class,
            read: true,
        ),
    ],
    mcp: [
        'get_delivery_schedule' => new McpTool(
            description: 'List the bookable delivery dates and the delivery windows of the day.',
            provider: DeliveryScheduleProvider::class,
        ),
    ],
)]
final readonly class DeliveryScheduleResource
{
    /**
     * @param  list<string>  $dates
     * @param  array<int, DeliverySlot>  $slots
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The delivery schedule identifier')]
        public string $id,
        public array $dates,
        public array $slots,
    ) {}
}
