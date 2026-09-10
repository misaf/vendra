<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

final class TenantTableRegistry
{
    /** @var array<string, array{table: string, connection: ?string}> */
    private array $tables = [];

    public function register(string ...$tables): void
    {
        $this->registerOnConnection(null, ...$tables);
    }

    public function registerOnConnection(?string $connection, string ...$tables): void
    {
        foreach ($tables as $table) {
            if ($table !== '') {
                $this->tables[$connection."\0".$table] = [
                    'table' => $table,
                    'connection' => $connection,
                ];
            }
        }
    }

    /**
     * @return list<array{table: string, connection: ?string}>
     */
    public function all(): array
    {
        ksort($this->tables);

        return array_values($this->tables);
    }
}
