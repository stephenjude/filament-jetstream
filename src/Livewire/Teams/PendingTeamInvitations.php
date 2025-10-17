<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Actions\Action;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Mail\TeamInvitation;
use Filament\Jetstream\Models\Team;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

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
            ->recordActions([
                Action::make('resendTeamInvitation')
                    ->label(__('filament-jetstream.action.resend_team_invitation.label'))
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::check('updateTeamMember', $this->team))
                    ->action(fn ($record) => $this->resendTeamInvitation($record)),
                Action::make('cancelTeamInvitation')
                    ->label(__('filament-jetstream.action.cancel_team_invitation.label'))
                    ->color('danger')
                    ->visible(fn () => Gate::check('removeTeamMember', $this->team))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->cancelTeamInvitation($this->team, $record)),
            ]);
    }

    public function resendTeamInvitation(Model $invitation)
    {
        Mail::to($invitation->email)->send(new TeamInvitation($invitation));

        $this->sendNotification(__('filament-jetstream.notification.team_invitation_sent.success.message'));
    }

    public function cancelTeamInvitation(Team $team, Model $invitation)
    {
        $invitation->delete();

        $team->fresh();

        $this->sendNotification(
            __('filament-jetstream.notification.team_invitation_cancelled.success.message')
        );
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.pending-team-invitations');
    }
}
