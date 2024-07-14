<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Termehsoft\Geographical\Models\GeographicalCountry;

final class GeographicalSync extends Command
{
    protected $description = 'Import Geographical data';

    protected $signature = 'app:geographical-sync';

    public function handle()
    {
        $this->info('Importing Countries and States');

        $existing = GeographicalCountry::pluck('iso_code');

        $countries = Http::get('http://data.lunarphp.io/countries+states.json')->object();

        $newCountries = collect($countries)->filter(fn($country) => ! $existing->contains($country->iso_code));

        if ( ! $newCountries->count()) {
            $this->info('There are no new countries to import');

            return Command::SUCCESS;
        }

        DB::transaction(function () use ($newCountries): void {
            $this->withProgressBar($newCountries, function ($country): void {
                $model = GeographicalCountry::create([
                    'name'              => $country->name,
                    'iso_code2'         => $country->iso2,
                    'iso_code3'         => $country->iso3,
                    'currency_iso_code' => $country->currency,
                    'native'            => $country->native,
                    'emoji'             => $country->emoji,
                    'emoji_u'           => $country->emojiU,
                ]);

                $states = collect($country->states)->map(function ($state) {
                    return [
                        'name' => $state->name,
                        'code' => $state->state_code,
                    ];
                });

                $model->states()->createMany($states->toArray());
            });
        });

        $this->line('');

        return Command::SUCCESS;
    }
}
