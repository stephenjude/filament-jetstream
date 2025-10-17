@php use Filament\Jetstream\Jetstream; @endphp
<x-filament-panels::page>
    @if (Jetstream::plugin()?->hasApiTokensFeatures())
        @livewire(config('filament-jetstream.livewire_components.api_tokens.create', \Filament\Jetstream\Livewire\ApiTokens\CreateApiToken::class))

        @livewire(config('filament-jetstream.livewire_components.api_tokens.manage', \Filament\Jetstream\Livewire\ApiTokens\ManageApiTokens::class))
    @endif
</x-filament-panels::page>
