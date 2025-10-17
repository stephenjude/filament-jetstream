<x-filament::section aside>
    <x-slot name="heading">
        {{ __('filament-jetstream.manage_api_tokens.section.title') }}
    </x-slot>
    <x-slot name="description">
        {{ __('filament-jetstream.manage_api_tokens.section.description') }}
    </x-slot>

    {{ $this->table }}

    <x-filament-actions::modals/>

</x-filament::section>
