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
    public string $teamModel = Team::class;

    public string $roleModel = Role::class;

    public string $membershipModel = Membership::class;

    public string $teamInvitationModel = TeamInvitation::class;

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
            Route::get('/team-invitations/{invitation}', fn($invitation) => $this->acceptTeamInvitation === null
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
        return $this->teamModel;
    }

    public function membershipModel(): string
    {
        return $this->membershipModel;
    }

    public function teamInvitationModel(): string
    {
        return $this->teamInvitationModel;
    }

    public function roleModel(): string
    {
        return $this->roleModel;
    }

    public function defaultAcceptTeamInvitation(string|int $invitationId): RedirectResponse
    {
        // Use custom acceptance logic if available
        if (class_exists('App\\Actions\\Jetstream\\AcceptTeamInvitation')) {
            return app('App\\Actions\\Jetstream\\AcceptTeamInvitation')->accept($invitationId);
        }

        // Fallback to default implementation
        return $this->handleTeamInvitationAcceptance($invitationId);
    }

    /**
     * Handle team invitation acceptance with default logic.
     */
    protected function handleTeamInvitationAcceptance(string|int $invitationId): RedirectResponse
    {
        $model = Jetstream::plugin()->teamInvitationModel();

        $invitation = $model::whereKey($invitationId)->with('team')->firstOrFail();

        $team = $invitation->team;

        // Check if user exists
        $existingUser = Jetstream::plugin()->userModel()::where('email', $invitation->email)->first();

        if ($existingUser) {
            // Handle existing user
            return $this->addExistingUserToTeam($existingUser, $invitation, $team);
        } else {
            // Handle non-registered user - redirect to registration if available
            return $this->handleUnregisteredUserInvitation($invitation);
        }
    }

    /**
     * Add an existing user to the team.
     */
    protected function addExistingUserToTeam($user, $invitation, $team): RedirectResponse
    {
        abort_if(
            boolean: $team->hasUserWithEmail($user->email),
            code: 403,
            message: __('filament-jetstream::default.action.add_team_member.error_message.email_already_joined')
        );

        AddingTeamMember::dispatch($team, $user);

        $team->users()->attach($user, ['role' => $invitation->role]);

        $user->switchTeam($team);

        TeamMemberAdded::dispatch($team, $user);

        $invitation->delete();

        Notification::make()
            ->success()
            ->title(__('filament-jetstream::default.notification.accepted_invitation.success.title'))
            ->body(
                __('filament-jetstream::default.notification.accepted_invitation.success.message', [
                    'team' => $invitation->team->name,
                ])
            )
            ->send();

        return redirect()->to(Filament::getHomeUrl());
    }

    /**
     * Handle invitation for unregistered users.
     */
    protected function handleUnregisteredUserInvitation($invitation): RedirectResponse
    {
        // Store invitation ID in session for after registration
        session(['pending_team_invitation' => $invitation->id]);

        // Check if registration is enabled
        if (Filament::hasRegistration()) {
            // Redirect to registration with message
            Notification::make()
                ->warning()
                ->title(__('Registration Required'))
                ->body(__('Please create an account first to accept this team invitation.'))
                ->send();

            return redirect()->to(Filament::getRegistrationUrl());
        }

        // If registration is not enabled, create user automatically as fallback
        return $this->createUserFromInvitation($invitation);
    }

    /**
     * Create a new user from invitation (fallback when registration is disabled).
     */
    protected function createUserFromInvitation($invitation): RedirectResponse
    {
        $newTeamMember = Jetstream::plugin()->userModel()::create([
            'name' => $this->extractNameFromEmail($invitation->email),
            'email' => $invitation->email,
            'password' => bcrypt(Str::password()),
        ]);

        $newTeamMember->forceFill([
            'email_verified_at' => now(),
        ])->save();

        AddingTeamMember::dispatch($invitation->team, $newTeamMember);

        $invitation->team->users()->attach($newTeamMember, ['role' => $invitation->role]);

        $newTeamMember->switchTeam($invitation->team);

        TeamMemberAdded::dispatch($invitation->team, $newTeamMember);

        $invitation->delete();

        Notification::make()
            ->success()
            ->title(__('filament-jetstream::default.notification.accepted_invitation.success.title'))
            ->body(
                __('filament-jetstream::default.notification.accepted_invitation.success.message', [
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

    /**
     * Extract a name from the email address.
     */
    protected function extractNameFromEmail(string $email): string
    {
        return ucfirst(explode('@', $email)[0]);
    }
}
