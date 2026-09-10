<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Misaf\VendraTransaction\Database\Factories\TransactionMetadataFactory;

/**
 * @property int $id
 * @property int $transaction_id
 * @property string $key_name
 * @property string $key_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['transaction_id', 'key_name', 'key_value'])]
#[UseFactory(TransactionMetadataFactory::class)]
final class TransactionMetadata extends Model
{
    /** @use HasFactory<TransactionMetadataFactory> */
    use HasFactory;

    protected $table = 'transaction_metadata';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'transaction_id' => 'integer',
            'key_name' => 'string',
            'key_value' => 'string',
        ];
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
