<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Contracts;

/**
 * Every fillable attribute except `id` is logged; a model may exclude more by
 * declaring `activityLogExcept(): array<int, string>`.
 *
 * A settings class may implement it too, and then each save that changes a
 * value is logged with the old and new values.
 */
interface ShouldLogActivity {}
