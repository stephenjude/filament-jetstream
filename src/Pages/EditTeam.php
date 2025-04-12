<?php

namespace Filament\Jetstream\Pages;

use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Jetstream\Livewire\Teams\AddTeamMember;
use Filament\Jetstream\Livewire\Teams\DeleteTeam;
use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Livewire\Teams\TeamMembers;
use Filament\Jetstream\Livewire\Teams\UpdateTeamName;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditTeam extends EditTenantProfile
{
    protected static string $view = 'filament-jetstream::pages.edit-team';

    protected static ?int $navigationSort = 2;

    public function form(Form $form): Form
    {
        return $form->schema([
            Livewire::make(UpdateTeamName::class)
                ->data(['team' => $this->tenant]),
            Livewire::make(AddTeamMember::class)
                ->data(['team' => $this->tenant]),
            Livewire::make(PendingTeamInvitations::class)
                ->data(['team' => $this->tenant]),
            Livewire::make(TeamMembers::class)
                ->data(['team' => $this->tenant]),
            Livewire::make(DeleteTeam::class)
                ->data(['team' => $this->tenant]),
        ]);
    }

    public static function getLabel(): string
    {
        return __('filament-jetstream::default.page.edit_team.title');
    }
}
