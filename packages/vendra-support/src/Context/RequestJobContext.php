<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Context;

use Closure;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

/**
 * Structured observability metadata for the current request, job, or command.
 *
 * Only cross-cutting identifiers every layer shares are first-class here: trace
 * id, actor, operation, tenant, and the hidden idempotency key. Domain-specific
 * keys — reseller, subscription, payment, panel, newsletter, … — are supplied by
 * their owning package through the generic {@see $metadata} bag, keeping this
 * support-layer value object free of domain vocabulary.
 */
final readonly class RequestJobContext
{
    public const string ACTOR_ID = 'actor_id';

    public const string ACTOR_TYPE = 'actor_type';

    public const string IDEMPOTENCY_KEY = 'idempotency_key';

    public const string OPERATION = 'operation';

    public const string TENANT_ID = 'tenant_id';

    public const string TRACE_ID = 'trace_id';

    /**
     * @param  array<string, int|string|null>  $metadata  Domain-owned visible keys; null values are omitted.
     */
    public function __construct(
        public ?string $traceId = null,
        public int|string|null $actorId = null,
        public ?string $actorType = null,
        public ?string $operation = null,
        public int|string|null $tenantId = null,
        public ?string $idempotencyKey = null,
        public array $metadata = [],
    ) {}

    public static function current(): self
    {
        return new self(
            traceId: self::string(Context::get(self::TRACE_ID)),
            actorId: self::identifier(Context::get(self::ACTOR_ID)),
            actorType: self::string(Context::get(self::ACTOR_TYPE)),
            operation: self::string(Context::get(self::OPERATION)),
            tenantId: self::identifier(Context::get(self::TENANT_ID)),
            idempotencyKey: self::string(Context::getHidden(self::IDEMPOTENCY_KEY)),
            metadata: self::currentMetadata(),
        );
    }

    public static function resolveTraceId(): string
    {
        return self::string(Context::get(self::TRACE_ID)) ?? (string) Str::uuid();
    }

    /**
     * Remove the given visible keys from the current context.
     */
    public static function forget(string ...$keys): void
    {
        Context::forget($keys);
    }

    public function add(): void
    {
        Context::add($this->toArray());
        $hiddenContext = $this->toHiddenArray();

        if ($hiddenContext !== []) {
            Context::addHidden($hiddenContext);
        }
    }

    public function scope(Closure $callback): mixed
    {
        return Context::scope(
            $callback,
            data: $this->toArray(),
            hidden: $this->toHiddenArray(),
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        $context = [];

        foreach ([
            self::TRACE_ID => $this->traceId,
            self::ACTOR_ID => $this->actorId,
            self::ACTOR_TYPE => $this->actorType,
            self::OPERATION => $this->operation,
            self::TENANT_ID => $this->tenantId,
        ] as $key => $value) {
            if ($value !== null) {
                $context[$key] = $value;
            }
        }

        foreach ($this->metadata as $key => $value) {
            if ($value !== null) {
                $context[$key] = $value;
            }
        }

        return $context;
    }

    /**
     * @return array<string, string>
     */
    public function toHiddenArray(): array
    {
        return $this->idempotencyKey === null
            ? []
            : [self::IDEMPOTENCY_KEY => $this->idempotencyKey];
    }

    /**
     * Visible context keys that are not first-class properties, snapshotted so
     * domain metadata round-trips through {@see current()}.
     *
     * @return array<string, int|string>
     */
    private static function currentMetadata(): array
    {
        $core = [
            self::TRACE_ID,
            self::ACTOR_ID,
            self::ACTOR_TYPE,
            self::OPERATION,
            self::TENANT_ID,
        ];

        $metadata = [];

        foreach (Context::all() as $key => $value) {
            if (is_string($key) && ! in_array($key, $core, true) && (is_int($value) || is_string($value))) {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }

    private static function identifier(mixed $value): int|string|null
    {
        return is_int($value) || is_string($value) ? $value : null;
    }

    private static function string(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
