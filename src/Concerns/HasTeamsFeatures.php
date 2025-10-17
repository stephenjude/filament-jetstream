<?php

namespace Filament\Jetstream\Concerns;

use Closure;
use Filament\Facades\Filament;
use Filament\Jetstream\Events\AddingTeamMember;
use Filament\Jetstream\Events\TeamMemberAdded;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Models\Membership;
use Filament\Jetstream\Models\Team;
use Filament\Jetstream\Models\TeamInvitation;
use Filament\Jetstream\Role;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait HasTeamsFeatures
{
    public string $teamModel;

    public string $roleModel;

    public string $membershipModel;

    public string $teamInvitationModel;

    public Closure|bool $hasTeamFeature = false;

    public ?Closure $acceptTeamInvitation = null;

    public function hasTeamsFeatures(): bool
    {
        return $this->evaluate($this->hasTeamFeature) === true;
    }

    public function teams(Closure|bool $condition = true, ?Closure $acceptTeamInvitation = null): static
    {
        $this->hasTeamFeature = $condition;

        $this->acceptTeamInvitation = $acceptTeamInvitation;

        return $this;
    }

    /**
     * @return array<int, Role>
     */
    public function getTeamRolesAndPermissions(): array
    {
        return $this->roleModel::roles()->toArray();
    }

    public function teamsRoutes(): array
    {
        return [
            Route::get('/team-invitations/{invitation}', fn ($invitation) => $this->acceptTeamInvitation === null
                ? $this->defaultAcceptTeamInvitation($invitation)
                : $this->evaluate($this->acceptTeamInvitation, ['invitationId' => $invitation]))
                ->middleware(['signed'])
                ->name('team-invitations.accept'),
        ];
    }

    public function configureTeamModels(
        string $teamModel = Team::class,
        string $roleModel = Role::class,
        string $membershipModel = Membership::class,
        string $teamInvitationModel = TeamInvitation::class
    ): static {
        $this->teamModel = $teamModel;

        $this->roleModel = $roleModel;

        $this->membershipModel = $membershipModel;

        $this->teamInvitationModel = $teamInvitationModel;

        return $this;
    }

    public function teamModel(): string
    {
        return $this->teamModel ?? config('filament-jetstream.team_model', Team::class);
    }

    public function membershipModel(): string
    {
        return $this->membershipModel ?? config('filament-jetstream.membership_model', Membership::class);
    }

    public function teamInvitationModel(): string
    {
        return $this->teamInvitationModel ?? config('filament-jetstream.team_invitation_model', TeamInvitation::class);
    }

    public function roleModel(): string
    {
        return $this->roleModel ?? config('filament-jetstream.role_model', Role::class);
    }

    public function defaultAcceptTeamInvitation(string|int $invitationId): RedirectResponse
    {
        $model = Jetstream::plugin()->teamInvitationModel();

        $invitation = $model::whereKey($invitationId)->with('team')->firstOrFail();

        $team = $invitation->team;

        $newTeamMember = Jetstream::plugin()->userModel()::firstOrCreate(['email' => $invitation->email], [
            'password' => bcrypt(Str::password()),
        ]);

        $newTeamMember->forceFill([
            'email_verified_at' => now(),
        ])->save();

        abort_if(
            boolean: $team->hasUserWithEmail($newTeamMember->email),
            code: 403,
            message: __('filament-jetstream.action.add_team_member.error_message.email_already_joined')
        );

        AddingTeamMember::dispatch($team, $newTeamMember);

        $team->users()->attach(
            $newTeamMember,
            ['role' => $invitation->role]
        );

        $newTeamMember->switchTeam($team);

        TeamMemberAdded::dispatch($team, $newTeamMember);

        $invitation->delete();

        Notification::make()
            ->success()
            ->title(__('filament-jetstream.notification.accepted_invitation.success.title'))
            ->body(
                __('filament-jetstream.notification.accepted_invitation.success.message', [
                    'team' => $invitation->team->name,
                ])
            )
            ->send();

        $passwordResetUrl = null;

        Password::broker(Filament::getAuthPasswordBroker())
            ->sendResetLink(
                credentials: ['email' => $newTeamMember->email],
                callback: function (CanResetPassword $user, string $token) use (&$passwordResetUrl) {
                    return $passwordResetUrl = Filament::getResetPasswordUrl($token, $user);
                }
            );

        return redirect()->to($passwordResetUrl ?? Filament::getHomeUrl());
    }
}
