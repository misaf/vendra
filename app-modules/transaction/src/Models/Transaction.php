<?php

declare(strict_types=1);

namespace Termehsoft\Transaction\Models;

use App\Casts\DateCast;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\Tenant\Models\Tenant;
use Termehsoft\Transaction\Enums\TransactionStatusEnum;

final class Transaction extends Tenant
{
    use SoftDeletes;

    protected $casts = [
        'id'             => 'integer',
        'reference_code' => 'string',
        'amount'         => 'integer',
        'status'         => TransactionStatusEnum::class,
        'created_at'     => DateCast::class,
        'updated_at'     => DateCast::class,
        'deleted_at'     => DateCast::class,
    ];

    protected $fillable = [
        'reference_code',
        'amount',
        'status',
    ];

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
