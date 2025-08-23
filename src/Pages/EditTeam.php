<?php

namespace Filament\Jetstream\Pages;

use Filament\Jetstream\Livewire\Teams\AddTeamMember;
use Filament\Jetstream\Livewire\Teams\DeleteTeam;
use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Livewire\Teams\TeamMembers;
use Filament\Jetstream\Livewire\Teams\UpdateTeamName;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class EditTeam extends EditTenantProfile
{
    protected string $view = 'filament-jetstream::pages.edit-team';

    protected static ?int $navigationSort = 2;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
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
