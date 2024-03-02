<x-filament-panels::page>
    @livewire(Laravel\Jetstream\Http\Livewire\UpdateTeamNameForm::class, compact('team'))

    @livewire(Laravel\Jetstream\Http\Livewire\TeamMemberManager::class, compact('team'))

    @livewire(Laravel\Jetstream\Http\Livewire\DeleteTeamForm::class, compact('team'))
</x-filament-panels::page>
