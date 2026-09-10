<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

final readonly class TagRelationship
{
    /**
     * @param  class-string<Model>  $model
     * @param  class-string<MorphPivot>  $pivotModel
     */
    public function __construct(
        public string $model,
        public string $morphName = 'taggable',
        public string $table = 'taggables',
        public string $foreignPivotKey = 'taggable_id',
        public string $relatedPivotKey = 'tag_id',
        public string $pivotModel = MorphPivot::class,
    ) {}
}
