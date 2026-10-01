<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Error;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Exceptions;
use Misaf\VendraSupport\Capabilities\IntegrationExceptions;
use PDOException;

it('keeps missing integration tables quiet', function (string $state, int $code, string $message): void {
    Exceptions::fake();
    $cause = new PDOException($message);
    $cause->errorInfo = [$state, $code, $message];

    IntegrationExceptions::report(new QueryException('testing', 'select * from missing', [], $cause));

    Exceptions::assertNothingReported();
})->with([
    'mysql' => ['42S02', 1146, 'Table does not exist'],
    'postgresql' => ['42P01', 7, 'Relation does not exist'],
    'sqlite' => ['HY000', 1, 'no such table: missing'],
]);

it('reports programming errors', function (): void {
    Exceptions::fake();
    $exception = new Error('Invalid resolver implementation.');

    IntegrationExceptions::report($exception);

    Exceptions::assertReported(fn (Error $reported): bool => $reported === $exception);
});

it('reports unexpected database failures', function (string $state, int $code, string $message): void {
    Exceptions::fake();
    $cause = new PDOException($message);
    $cause->errorInfo = [$state, $code, $message];
    $exception = new QueryException('testing', 'select * from catalog', [], $cause);

    IntegrationExceptions::report($exception);

    Exceptions::assertReported(fn (QueryException $reported): bool => $reported === $exception);
})->with([
    'connection failure' => ['HY000', 2002, 'Connection refused'],
    'missing column' => ['42S22', 1054, 'Unknown column'],
    'sqlite syntax error' => ['HY000', 1, 'near select: syntax error'],
]);
