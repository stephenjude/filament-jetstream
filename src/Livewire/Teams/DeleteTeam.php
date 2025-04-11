<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Illuminate\View\View;

class DeleteTeam extends BaseLivewireComponent
{
    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament-jetstream::default.delete_team.section.title'))
                    ->description(__('filament-jetstream::default.delete_team.section.description'))
                    ->aside()
                    ->schema([
                        Forms\Components\Placeholder::make('notice')
                            ->hiddenLabel()
                            ->content(fn() => __('filament-jetstream::default.delete_team.section.notice')),
                        Actions::make([
                            Actions\Action::make('deleteAccountAction')
                                ->label(__('filament-jetstream::default.action.delete_team.label'))
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading(__('filament-jetstream::default.delete_team.section.title'))
                                ->modalDescription(__('filament-jetstream::default.action.delete_team.notice'))
                                ->modalSubmitActionLabel(__('filament-jetstream::default.action.delete_team.label'))
                                ->modalCancelAction(false)
                                ->action(fn() => $this->deleteTeam($this->team)),
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
