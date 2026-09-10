<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Data;

use Illuminate\Database\Eloquent\Model;

/**
 * A priced line handed to `PlaceOrderAction`.
 *
 * The order module never reads the catalog: the caller resolves the sellable's
 * translatable name and its unit amount in minor units, and this draft carries
 * that decision into the immutable order line.
 */
final readonly class OrderLineDraft
{
    /**
     * @param  array<string, string>  $name  Translatable name keyed by locale.
     * @param  int  $unitAmount  Unit price in minor units.
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public Model $sellable,
        public array $name,
        public int $unitAmount,
        public int $quantity = 1,
        public ?array $metadata = null,
    ) {}

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
