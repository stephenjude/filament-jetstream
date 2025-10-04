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
use Laravel\SerializableClosure\SerializableClosure;

trait HasTeamsFeatures
{
    public string $teamModel = Team::class;

    public string $roleModel = Role::class;

    public string $membershipModel = Membership::class;

    public string $teamInvitationModel = TeamInvitation::class;

    public mixed $hasTeamFeature = false;

    public mixed $acceptTeamInvitation = null;

    public function hasTeamsFeatures(): bool
    {
        return $this->evaluate($this->hasTeamFeature) === true;
    }

    public function teams(Closure | bool $condition = true, Closure | string | null $acceptTeamInvitation = null): static
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
            Route::get('/team-invitations/{invitation}', function ($invitation) {
                if ($this->acceptTeamInvitation === null) {
                    return $this->defaultAcceptTeamInvitation($invitation);
                }

                if (is_string($this->acceptTeamInvitation)) {
                    return app()->call($this->acceptTeamInvitation, ['invitationId' => $invitation]);
                }

                // Unwrap the closure if it's serialized
                $closure = $this->unwrapClosure($this->acceptTeamInvitation);

                return $this->evaluate($closure, ['invitationId' => $invitation]);
            })
                ->middleware(['signed'])
                ->name('team-invitations.accept'),
        ];
    }

    /**
     * Unwrap a closure if it's wrapped in a SerializableClosure or serializer object.
     */
    protected function unwrapClosure(mixed $closure): mixed
    {
        // Handle SerializableClosure wrapper
        if ($closure instanceof SerializableClosure) {
            return $closure->getClosure();
        }

        // Handle Laravel's native serializer wrapper
        if (is_object($closure) && method_exists($closure, 'getClosure')) {
            return $closure->getClosure();
        }

        return $closure;
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

    public function defaultAcceptTeamInvitation(string | int $invitationId): RedirectResponse
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
            message: __('filament-jetstream::default.action.add_team_member.error_message.email_already_joined')
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
     * Serialize the trait for caching.
     *
     * This method properly handles closure serialization to prevent
     * type errors when running php artisan optimize.
     */
    public function __serialize(): array
    {
        $data = get_object_vars($this);

        // Convert closures to SerializableClosure instances
        foreach ($data as $key => $value) {
            if ($value instanceof Closure) {
                $data[$key] = new SerializableClosure($value);
            }
        }

        return $data;
    }

    /**
     * Unserialize the trait from cache.
     *
     * This method properly handles closure deserialization to restore
     * the trait state after php artisan optimize.
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            // Convert SerializableClosure back to Closure
            if ($value instanceof SerializableClosure) {
                $this->{$key} = $value->getClosure();
            } elseif (is_object($value) && method_exists($value, 'getClosure')) {
                // Handle Laravel's native serializer wrapper
                $this->{$key} = $value->getClosure();
            } else {
                $this->{$key} = $value;
            }
        }
    }
}
