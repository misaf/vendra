<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Contracts;

/**
 * Every fillable attribute except `id` is logged; a model may exclude more by
 * declaring `activityLogExcept(): array<int, string>`.
 */
interface ShouldLogActivity {}
