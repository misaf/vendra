<x-dynamic-component :component="$getFieldWrapperView()" :$field>
    <div id="{{ $getStatePath() }}" 
         wire:ignore
         x-init="() => {
            const options = {
                'callback': (token) => {
                    $wire.set('{{ $getStatePath() }}', token);
                },
            };

            window.onloadTurnstileCallback = () => {
                turnstile.render($refs.turnstile, options);
            };

            window.resetTurnstile = () => {
                turnstile.reset($refs.turnstile);
            };
        }">
        <div
            data-language="{{ app()->getLocale() }}"
            data-sitekey="{{ config('services.turnstile.key') }}"
            data-size="{{ $getSize() }}"
            :data-theme="theme"
            x-ref="turnstile">
        </div>
    </div>
</x-dynamic-component>

@once
    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback" defer></script>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('resetTurnstile', () => {
                    resetTurnstile();
                });
            });
        </script>
    @endpush
@endonce
