<x-filament-panels::page>

    @livewire(Filament\Jetstream\Livewire\Teams\UpdateTeamName::class)

    @if(Gate::check('addTeamMember', \Filament\Facades\Filament::getTenant()))

        @livewire(Filament\Jetstream\Livewire\Teams\AddTeamMember::class)

        @livewire(Filament\Jetstream\Livewire\Teams\PendingTeamInvitations::class)

    @endif

    @livewire(Filament\Jetstream\Livewire\Teams\TeamMembers::class)

    @livewire(Filament\Jetstream\Livewire\Teams\DeleteTeam::class)

</x-filament-panels::page>
