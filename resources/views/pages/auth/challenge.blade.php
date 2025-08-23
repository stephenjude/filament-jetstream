<x-filament-panels::page.simple>
    <x-slot name="subheading">
        {{ __('filament-jetstream::default.form.or.label') }}
        {{ $this->recoveryAction }}
    </x-slot>

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}
    </x-filament-panels::form>

</x-filament-panels::page.simple>
