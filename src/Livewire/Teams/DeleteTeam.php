<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DeleteTeam extends BaseLivewireComponent
{
    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('notice')
                    ->hiddenLabel()
                    ->state(__('filament-jetstream::default.delete_team.section.notice')),
                Actions::make([
                    Action::make('deleteAccountAction')
                        ->label(__('filament-jetstream::default.action.delete_team.label'))
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-jetstream::default.delete_team.section.title'))
                        ->modalDescription(__('filament-jetstream::default.action.delete_team.notice'))
                        ->modalSubmitActionLabel(__('filament-jetstream::default.action.delete_team.label'))
                        ->modalCancelAction(false)
                        ->action(fn() => $this->deleteTeam($this->team)),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('filament-jetstream::livewire.teams.delete-team');
    }

    public function deleteTeam(Team $team): void
    {
        $team->purge();

        $this->sendNotification(__('filament-jetstream::default.notification.team_deleted.success.message'));

        redirect()->to(Filament::getCurrentPanel()?->getUrl());
    }
}
