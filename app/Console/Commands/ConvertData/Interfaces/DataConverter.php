<?php

declare(strict_types=1);

namespace App\Console\Commands\ConvertData\Interfaces;

interface DataConverter
{
    /**
     * Migrate data.
     *
     * @return void
     */
    public function migrate(): void;
}
