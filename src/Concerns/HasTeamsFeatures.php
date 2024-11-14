<?php

namespace Filament\Jetstream\Concerns;

use App\Models\User;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

trait HasTeamsFeatures
{
    public Closure | bool $hasTeamFeature = false;

    public ?Closure $acceptTeamInvitation = null;

    public string $userModel = 'App\\Models\\User';

    public string $teamModel = Team::class;

    public string $membershipModel = Membership::class;

    public string $teamInvitationModel = TeamInvitation::class;

    /** @var array{name:string, key:string, description:string, permissions:array<int, string>}|null */
    public ?array $rolesAndPermissions = [
        [
            'key' => 'admin',
            'name' => 'Administrator',
            'description' => 'Administrator users can perform any action.',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        [
            'key' => 'editor',
            'name' => 'Editor',
            'description' => 'Editor users have the ability to read, create, and update.',
            'permissions' => [
                'read',
                'create',
                'update',
            ],
        ],
    ];

    public function hasTeamsFeatures(): bool
    {
        return $this->evaluate($this->hasTeamFeature) === true;
    }

    /**
     * @param  Closure|array{name:string, key:string, description:string, permissions:array<int, string>}  $rolesAndPermissions
     */
    public function teams(Closure | bool $condition = true, Closure | array | null $rolesAndPermissions = null, ?Closure $acceptTeamInvitation = null): static
    {
        $this->hasTeamFeature = $condition;

        $this->acceptTeamInvitation = $acceptTeamInvitation;

        $this->rolesAndPermissions = $rolesAndPermissions ?? $this->rolesAndPermissions;

        return $this;
    }

    public function useTeamsModels(string $userModel = 'App\\Models\\User', string $teamModel = Team::class, string $membershipModel = Membership::class, string $teamInvitationModel = TeamInvitation::class): static
    {
        $this->userModel = $userModel;

        $this->teamModel = $teamModel;

        $this->membershipModel = $membershipModel;

        $this->teamInvitationModel = $teamInvitationModel;

        return $this;
    }

    public function userModel(): string
    {
        return $this->userModel;
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
            Route::get('/team-invitations/{invitation}', fn ($invitation) => $this->acceptTeamInvitation === null
                ? $this->defaultAcceptTeamInvitation($invitation)
                : $this->evaluate($this->acceptTeamInvitation, ['invitationId' => $invitation]))
                ->middleware(['signed'])
                ->name('team-invitations.accept'),
        ];
    }

    public function defaultAcceptTeamInvitation(string | int $invitationId): RedirectResponse
    {
        $model = Jetstream::plugin()->teamInvitationModel;

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        $team = $invitation->team;

        $email = $invitation->email;

        $role = $invitation->role;

        $user = $team->owner;

        Gate::forUser($user)->authorize('addTeamMember', $team);

        abort_unless(User::where('email', $email)->exists(), __('We were unable to find a registered user with this email address.'));

        abort_if($team->hasUserWithEmail($email), __('This user already belongs to the team.'));

        $newTeamMember = (new $this->userModel)->where('email', $email)->firstOrFail();

        AddingTeamMember::dispatch($team, $newTeamMember);

        $team->users()->attach(
            $newTeamMember,
            ['role' => $role]
        );

        TeamMemberAdded::dispatch($team, $newTeamMember);

        $invitation->delete();

        Notification::make()
            ->success()
            ->title(__('Team Invitation Accepted'))
            ->body(__('Great! You have accepted the invitation to join the :team team.', ['team' => $invitation->team->name]))
            ->send();

        return redirect()->to(Filament::getHomeUrl());
    }
}
