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

        throw_if($relationship === null, LogicException::class, 'Install a tag provider to use tags.');

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

        $tags = $this->getRelationValue('tags');

        throw_unless($tags instanceof Collection, LogicException::class, 'The tags relationship did not load a collection.');

        return $tags;
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
        $findOrCreate = [static::getTagClassName(), 'findOrCreate'];

        throw_unless(is_callable($findOrCreate), LogicException::class, 'The tag model must provide findOrCreate().');

        // A single tag name resolves to one tag model rather than a collection.
        $found = $findOrCreate($tags, $this->tagType());
        $tagIds = [];

        foreach ($found instanceof Model ? [$found] : (is_iterable($found) ? $found : []) as $tag) {
            if ($tag instanceof Model) {
                $tagIds[] = $tag->getKey();
            }
        }

        $this->tags()->sync($tagIds);

        return $this;
    }

    /** @return class-string<Model> */
    public static function getTagClassName(): string
    {
        $relationship = TagIntegration::relationship();

        throw_if($relationship === null, LogicException::class, 'Install a tag provider to use tags.');

        return $relationship->model;
    }

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
