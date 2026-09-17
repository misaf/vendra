<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $attribute_value_id
 * @property string $selectable_type
 * @property int $selectable_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['attribute_value_id', 'selectable_type', 'selectable_id'])]
final class AttributeValueSelection extends Model
{
    /** @return BelongsTo<AttributeValue, $this> */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    /** @return MorphTo<Model, $this> */
    public function selectable(): MorphTo
    {
        return $this->morphTo();
    }
}
