<x-filament-panels::page.simple>
    <x-slot name="subheading">
        {{ __('filament-jetstream.form.or.label') }}
        {{ $this->challengeAction }}
    </x-slot>

    <form id="form" wire:submit="authenticate">
        {{ $this->form }}
    </form>

</x-filament-panels::page.simple>
