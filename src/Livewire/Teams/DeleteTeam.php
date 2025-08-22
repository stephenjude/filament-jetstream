<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
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
                Section::make(__('filament-jetstream::default.delete_team.section.title'))
                    ->description(__('filament-jetstream::default.delete_team.section.description'))
                    ->aside()
                    ->visible(fn () => Gate::check('delete', $this->team))
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
                                ->action(fn () => $this->deleteTeam($this->team)),
                        ]),
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
