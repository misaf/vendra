<?php

declare(strict_types=1);

namespace App\Console\Commands\ConvertData;

use App\Console\Commands\ConvertData\Converter\BlogDataConverter;
use App\Console\Commands\ConvertData\Converter\ProductDataConverter;
use App\Console\Commands\ConvertData\Interfaces\DataConverter;
use App\Console\Commands\ConvertData\Retriever\BlogDataRetriever;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ConvertData extends Command
{
    protected $description = 'Converts data from the old version to the new version';

    protected $signature = 'app:convert-data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $dataType = $this->choice(
            'Select the type of data to convert',
            ['Blog', 'Product', 'All'],
            3
        );

        if (null === $dataType) {
            $this->info('Operation canceled.');

            return Command::FAILURE;
        }

        $dataType = Str::lower($dataType . 'DataConverter');

        try {
            DB::transaction(function () use ($dataType): void {
                $this->convertData($dataType, new BlogDataConverter(
                    storage: Storage::disk('local'),
                    dataRetriever: new BlogDataRetriever()
                ));

                $this->convertData($dataType, new ProductDataConverter());
            });

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('An error occurred during data conversion.');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Convert data based on the provided converter.
     *
     * @param string $dataType
     * @param DataConverter $converter
     * @return void
     */
    private function convertData(string $dataType, DataConverter $converter): void
    {
        $converterName = class_basename($converter);

        if ($this->shouldConvert($dataType, $converterName)) {
            $this->info("Converting {$converterName} data...");

            $converter->migrate();

            $this->info("{$converterName} data conversion complete.");
        }
    }

    /**
     * Determine if the conversion should be performed.
     *
     * @param string $dataType
     * @param string $converterName
     * @return bool
     */
    private function shouldConvert(string $dataType, string $converterName): bool
    {
        return 'all' === $dataType || Str::lower($converterName) === $dataType;
    }
}
