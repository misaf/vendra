{{-- <x-filament-widgets::widget>
    <x-filament::section>
        <x-filament::link icon="heroicon-m-sparkles" :href="route('filament.admin.resources.affiliates.view', filament()->auth()->user()->latestAffiliate->slug)">
            لینک دعوتنامه اختصاصی شما

            <x-slot name="badge">
                {{ filament()->auth()->user()->affiliateUsers()->count() }}
            </x-slot>
        </x-filament::link>

        <x-filament::input.wrapper>
            <x-filament::input type="text" wire:model="name"/>
        </x-filament::input.wrapper>
    </x-filament::section>
</x-filament-widgets::widget> --}}


<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
            @php
                $from = [255, 0, 0];
                $to = [0, 0, 255];
            @endphp
            {{
                SimpleSoftwareIO\QrCode\Facades\QrCode::style('dot')
                ->eye('circle')
                ->size(200)
                ->gradient($from[0], $from[1], $from[2], $to[0], $to[1], $to[2], 'diagonal')
                ->margin(1)
                ->errorCorrection('H')
                ->generate(config('app.url') . '/' . filament()->auth()->user()->latestAffiliate->slug);
            }}
            {{-- <h2
                class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white"
            >
                {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ filament()->getUserName($user) }}
            </p> --}}

        <x-filament::link icon="heroicon-m-sparkles" :href="route('filament.admin.resources.affiliates.view', filament()->auth()->user()->latestAffiliate->slug)">
            لینک دعوتنامه اختصاصی شما

            <x-slot name="badge">
                {{ filament()->auth()->user()->affiliateUsers()->count() }}
            </x-slot>
        </x-filament::link>
    </x-filament::section>
</x-filament-widgets::widget>
