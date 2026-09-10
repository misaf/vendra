<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use ArrayAccess;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LogicException;

trait HasOptionalTags
{
    /** @return MorphToMany<Model, $this> */
    public function tags(): MorphToMany
    {
        $relationship = TagIntegration::relationship();

        if ($relationship === null) {
            throw new LogicException('Install a tag provider to use tags.');
        }

        $tagTable = (new $relationship->model)->getTable();

        return $this->morphToMany(
            $relationship->model,
            $relationship->morphName,
            $relationship->table,
            $relationship->foreignPivotKey,
            $relationship->relatedPivotKey,
            $this->getKeyName(),
        )
            ->using($relationship->pivotModel)
            ->where("{$tagTable}.type", $this->tagType());
    }

    abstract protected function tagType(): string;

    /** @return Collection<int, Model> */
    public function tagsWithType(?string $type = null): Collection
    {
        $this->assertTagType($type);

        return $this->getRelationValue('tags');
    }

    /** @param array<int, string>|ArrayAccess<int, string> $tags */
    public function syncTagsWithType(array|ArrayAccess $tags, ?string $type = null): static
    {
        $this->assertTagType($type);

        return $this->syncTags($tags);
    }

    /** @param string|array<int, string>|ArrayAccess<int, string> $tags */
    public function syncTags(string|array|ArrayAccess $tags): static
    {
        $className = static::getTagClassName();

        $tags = collect($className::findOrCreate($tags, $this->tagType()));

        $this->tags()->sync($tags->pluck('id')->toArray());

        return $this;
    }

    /** @return class-string<Model> */
    public static function getTagClassName(): string
    {
        $relationship = TagIntegration::relationship();

        if ($relationship === null) {
            throw new LogicException('Install a tag provider to use tags.');
        }

        return $relationship->model;
    }

    /**
     * The tags relation is constrained to this model's single tag type, so any
     * explicit type passed by callers such as Filament's Spatie tag components
     * must match it.
     */
    private function assertTagType(?string $type): void
    {
        if ($type !== null && $type !== $this->tagType()) {
            throw new LogicException(sprintf(
                'Tag type [%s] does not match the [%s] type declared by %s.',
                $type,
                $this->tagType(),
                static::class,
            ));
        }
    }
}
