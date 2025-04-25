<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Jetstream\Events\TeamMemberUpdated;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Filament\Jetstream\Role;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class TeamMembers extends BaseLivewireComponent implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function table(Table $table): Table
    {
        $model = Jetstream::plugin()->membershipModel();

        $teamForeignKeyColumn = Jetstream::getForeignKeyColumn(get_class($this->team));

        return $table
            ->query(fn () => $model::with('user')->where($teamForeignKeyColumn, $this->team->id))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ImageColumn::make('profile_photo_url')
                        ->disk(Jetstream::plugin()?->profilePhotoDisk())
                        ->defaultImageUrl(fn ($record): string => Filament::getUserAvatarUrl($record->user))
                        ->circular()
                        ->size(25)
                        ->grow(false),
                    Tables\Columns\TextColumn::make('user.email'),
                ]),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('updateTeamRole')
                    ->visible(fn ($record): bool => Gate::check('updateTeamMember', $this->team))
                    ->label(fn ($record): string => Role::find($record->role)->name)
                    ->modalWidth('lg')
                    ->modalHeading(__('filament-jetstream::default.action.update_team_role.title'))
                    ->modalSubmitActionLabel(__('filament-jetstream::default.action.save.label'))
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
                    ->action(fn ($record, array $data) => $this->updateTeamRole($this->team, $record, $data)),
                Tables\Actions\Action::make('removeTeamMember')
                    ->visible(
                        fn ($record): bool => $this->authUser()->id !== $record->id && Gate::check(
                            'removeTeamMember',
                            $this->team
                        )
                    )
                    ->label(__('filament-jetstream::default.action.remove_team_member.label'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->removeTeamMember($this->team, $record)),
                Tables\Actions\Action::make('leaveTeam')
                    ->visible(fn ($record): bool => $this->authUser()->id === $record->id)
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('danger')
                    ->label(__('filament-jetstream::default.action.leave_team.label'))
                    ->modalDescription(__('filament-jetstream::default.action.leave_team.notice'))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->leaveTeam()),
            ]);
    }

    public function updateTeamRole(Model $team, Model $teamMember, array $data): void
    {
        if (! Gate::check('updateTeamMember', $team)) {
            $this->sendNotification(
                __('filament-jetstream::default.notification.permission_denied.cannot_update_team_member'),
                type: 'danger'
            );

            return;
        }

        $team->users()->updateExistingPivot($teamMember->user_id, ['role' => $data['role']]);

        TeamMemberUpdated::dispatch($team->fresh(), $teamMember);

        $this->sendNotification();

        $team->fresh();
    }

    public function removeTeamMember(Team $team, Model $teamMember): void
    {
        if ($teamMember->id === $team->owner->id) {
            $this->sendNotification(
                __('filament-jetstream::default.notification.permission_denied.cannot_leave_team'),
                type: 'danger'
            );

            return;
        }

        if (! Gate::check('removeTeamMember', $team)) {
            $this->sendNotification(
                __('filament-jetstream::default.notification.permission_denied.cannot_remove_team_member'),
                type: 'danger'
            );

            return;
        }

        $team->removeUser($teamMember->user);

        $this->sendNotification(__('filament-jetstream::default.notification.team_member_removed.success.message'));

        $team->fresh();
    }

    public function leaveTeam(Team $team): void
    {
        $teamMember = $this->authUser();

        if ($teamMember->id === $team->owner->id) {
            $this->sendNotification(
                title: __('filament-jetstream::default.notification.permission_denied.cannot_leave_team'),
                type: 'danger'
            );

            return;
        }

        $team->removeUser($teamMember);

        $this->sendNotification(__('filament-jetstream::default.notification.leave_team.success'));

        $this->redirect(Filament::getHomeUrl());
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.team-members');
    }
}
