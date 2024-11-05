<?php

namespace Filament\Jetstream\Concerns;

use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Filament\Jetstream\Events\AddingTeamMember;
use Filament\Jetstream\Events\TeamMemberAdded;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Role;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

trait HasTeamsFeatures
{
    public Closure | bool $hasTeamFeature = false;

    public ?string $teamMenuItemLabel = null;

    public ?string $teamMenuItemIcon = null;

    public ?Closure $acceptTeamInvitation = null;

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

    public function teams(Closure | bool $condition = true, ?string $menuItemLabel = null, ?string $menuItemIcon = null): static
    {
        $this->hasTeamFeature = $condition;

        $this->teamMenuItemLabel = $menuItemLabel;

        $this->teamMenuItemIcon = $menuItemIcon;

        return $this;
    }

    public function handleAcceptTeamInvitation(Closure $acceptTeamInvitation): static
    {
        $this->acceptTeamInvitation = $acceptTeamInvitation;

        return $this;
    }

    /**
     * @param  Closure|array{name:string, key:string, description:string, permissions:array<int, string>}  $rolesAndPermissions
     */
    public function teamRolesAndPermissions(Closure | array $rolesAndPermissions): static
    {
        $this->rolesAndPermissions = $rolesAndPermissions;

        return $this;
    }

    /**
     * @return array<int, Role>
     */
    public function getTeamRolesAndPermissions(): array
    {
        return collect($this->evaluate($this->rolesAndPermissions))
            ->map(
                fn ($role) => Jetstream::role($role['key'], $role['name'], $role['permissions'])->description($role['description'])
            )
            ->toArray();
    }

    public function getTeamMenuItemLabel(): string
    {
        return $this->evaluate($this->teamMenuItemLabel) ?? __('Team Settings');
    }

    public function getTeamMenuItemIcon(): string
    {
        return $this->evaluate($this->teamMenuItemIcon) ?? __('heroicon-o-cog-6-tooth');
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
        $model = Jetstream::teamInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        $team = $invitation->team;

        $email = $invitation->email;

        $role = $invitation->role;

        $user = $team->owner;

        Gate::forUser($user)->authorize('addTeamMember', $team);

        abort_unless(User::where('email', $email)->exists(), __('We were unable to find a registered user with this email address.'));

        abort_if($team->hasUserWithEmail($email), __('This user already belongs to the team.'));

        $newTeamMember = Jetstream::findUserByEmailOrFail($email);

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
