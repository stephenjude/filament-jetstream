<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Facades\Filament;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PendingTeamInvitations extends BaseLivewireComponent implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->team->teamInvitations()->latest())
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('email'),
                ]),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('cancelTeamInvitation')
                    ->label(__('filament-jetstream::default.action.cancel_team_invitation.label'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->cancelTeamInvitation($this->team, $record)),
            ]);
    }

    public function cancelTeamInvitation(Team $team, Model $invitation)
    {
        $invitation->delete();

        $team->fresh();

        $this->sendNotification();
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.pending-team-invitations');
    }
}
