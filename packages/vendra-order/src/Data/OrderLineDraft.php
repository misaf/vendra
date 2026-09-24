<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Data;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class OrderLineDraft
{
    /**
     * The unit amount is in minor units.
     *
     * @param  array<string, string>  $name
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public Model $sellable,
        public array $name,
        public int $unitAmount,
        public int $quantity = 1,
        public ?array $metadata = null,
    ) {
        throw_if($quantity < 1, InvalidArgumentException::class, 'Quantity must be positive.');
    }

    public function lineAmount(): int
    {
        return $this->unitAmount * $this->quantity;
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(string $currencyCode): array
    {
        return [
            'sellable_type' => $this->sellable->getMorphClass(),
            'sellable_id' => $this->sellable->getKey(),
            'name' => $this->name,
            'currency_code' => $currencyCode,
            'quantity' => $this->quantity,
            'unit_amount' => $this->unitAmount,
            'line_amount' => $this->lineAmount(),
            'metadata' => $this->metadata,
        ];
    }
}
