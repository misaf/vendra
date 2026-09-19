<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\McpTool;
use Misaf\VendraDeliveryApi\State\DeliveryScheduleProvider;

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
