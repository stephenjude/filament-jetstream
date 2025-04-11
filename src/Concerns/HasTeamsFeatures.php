<?php

namespace Filament\Jetstream\Concerns;

use Closure;
use Filament\Facades\Filament;
use Filament\Jetstream\Events\AddingTeamMember;
use Filament\Jetstream\Events\TeamMemberAdded;
use Filament\Jetstream\Jetstream;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait HasTeamsFeatures
{
    public Closure|bool $hasTeamFeature = false;

    public ?Closure $acceptTeamInvitation = null;

    public function hasTeamsFeatures(): bool
    {
        return $this->evaluate($this->hasTeamFeature) === true;
    }

    /**
     * @param  Closure|array{name:string, key:string, description:string, permissions:array<int, string>}  $rolesAndPermissions
     */
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
        return collect($this->evaluate($this->rolesAndPermissions))
            ->map(
                fn ($role) => (new Role(
                    $role['key'],
                    $role['name'],
                    $role['permissions']
                ))->description($role['description'])
            )
            ->toArray();
    }

    public function teamsRoutes(): array
    {
        return [
            Route::get('/team-invitations/{invitation}', fn($invitation) => $this->acceptTeamInvitation === null
                ? $this->defaultAcceptTeamInvitation($invitation)
                : $this->evaluate($this->acceptTeamInvitation, ['invitationId' => $invitation]))
                ->middleware(['signed'])
                ->name('team-invitations.accept'),
        ];
    }

    public function defaultAcceptTeamInvitation(string|int $invitationId): RedirectResponse
    {
        $model = Jetstream::teamInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        $team = $invitation->team;

        $newTeamMember = Jetstream::userModel()::firstOrCreate(
            ['email' => $invitation->email],
            ['name' => '', 'password' => bcrypt(Str::password())]
        );

        abort_if(
            $team->hasUserWithEmail($newTeamMember->email),
            403,
            __('filament-jetstream::default.action.add_team_member.error_message.email_already_joined')
        );

        AddingTeamMember::dispatch($team, $newTeamMember);

        $team->users()->attach(
            $newTeamMember,
            ['role' => $invitation->role]
        );

        TeamMemberAdded::dispatch($team, $newTeamMember);

        $invitation->delete();

        Notification::make()
            ->success()
            ->title(__('filament-jetstream::default.notification.accepted_invitation.success.title'))
            ->body(
                __(
                    'filament-jetstream::default.notification.accepted_invitation.success.message',
                    ['team' => $invitation->team->name]
                )
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
