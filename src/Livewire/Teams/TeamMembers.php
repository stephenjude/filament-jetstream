<?php

namespace Filament\Jetstream\Livewire\Teams;

use App\Models\Membership;
use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Jetstream\Events\TeamMemberUpdated;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class TeamMembers extends BaseLivewireComponent implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Membership::with('user')->where('team_id', Filament::getTenant()->id))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ImageColumn::make('profile_photo_url')
                        ->disk(Jetstream::plugin()?->profilePhotoDisk())
                        ->defaultImageUrl(fn ($record): string => Filament::getUserAvatarUrl($record->user))
                        ->circular()
                        ->size(25)
                        ->grow(false),
                    Tables\Columns\TextColumn::make('user.name'),
                ]),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('updateTeamRole')
                    ->visible(fn ($record): bool => Gate::check('updateTeamMember', Filament::getTenant()))
                    ->label(fn ($record): string => Jetstream::findRole($record->role)->name)
                    ->modalWidth('lg')
                    ->modalHeading(__('filament-jetstream::default.actions.update_team_role.title'))
                    ->modalSubmitActionLabel(__('filament-jetstream::default.actions.save.label'))
                    ->modalCancelAction(false)
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->form([
                        Grid::make()
                            ->columns(1)
                            ->schema(function () {
                                $roles = collect(Jetstream::plugin()?->getTeamRolesAndPermissions());

                                return [
                                    Radio::make('role')
                                        ->hiddenLabel()
                                        ->required()
                                        ->in($roles->pluck('key'))
                                        ->options($roles->pluck('name', 'key'))
                                        ->descriptions($roles->pluck('description', 'key'))
                                        ->default(fn ($record) => $record->role),
                                ];
                            }),
                    ])
                    ->action(fn ($record, array $data) => $this->updateTeamRole($record, $data)),
                Tables\Actions\Action::make('removeTeamMember')
                    ->visible(fn ($record): bool => $this->authUser()->id !== $record->id && Gate::check('removeTeamMember', Filament::getTenant()))
                    ->label(__('filament-jetstream::default.actions.remove_team_member.label'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->removeTeamMember($record)),
                Tables\Actions\Action::make('leaveTeam')
                    ->visible(fn ($record): bool => $this->authUser()->id === $record->id)
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('danger')
                    ->label(__('filament-jetstream::default.actions.leave_team.label'))
                    ->modalDescription(__('filament-jetstream::default.actions.leave_team.notice'))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->leaveTeam()),
            ]);
    }

    public function updateTeamRole(Model $teamMember, array $data): void
    {
        /** @var Team $team */
        $team = Filament::getTenant();

        if (Gate::check('updateTeamMember', $team)) {
            $this->sendNotification(__('You do not have permission to update this team member.'), type: 'danger');

            return;
        }

        $team->users()->updateExistingPivot($teamMember->id, [
            'role' => $data['role'],
        ]);

        TeamMemberUpdated::dispatch($team->fresh(), $teamMember);

        $this->sendNotification();

        Filament::getTenant()->fresh();
    }

    public function removeTeamMember(Model $teamMember): void
    {
        /** @var Team $team */
        $team = Filament::getTenant();

        if ($teamMember->id === $team->owner->id) {
            $this->sendNotification(__('You may not leave a team that you created.'), type: 'danger');

            return;
        }

        if (Gate::check('removeTeamMember', $team)) {
            $this->sendNotification(__('You do not have permission to remove this team member.'), type: 'danger');

            return;
        }

        $team->removeUser($teamMember);

        $this->sendNotification(__('You have removed this team member.'));

        Filament::getTenant()->fresh();
    }

    public function leaveTeam(): void
    {
        $teamMember = $this->authUser();

        /** @var Team $team */
        $team = Filament::getTenant();

        if ($teamMember->id === $team->owner->id) {
            $this->sendNotification(__('You may not leave a team that you created.'), type: 'danger');

            return;
        }

        $team->removeUser($teamMember);

        $this->sendNotification(__('You have left the team.'));

        $this->redirect(Filament::getHomeUrl());
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.team-members');
    }
}
