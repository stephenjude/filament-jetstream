<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Closure;
use Filament\Jetstream\Contracts\InvitesTeamMembers;
use Filament\Jetstream\Events\InvitingTeamMember;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Mail\TeamInvitation;
use Filament\Jetstream\Rules\Role;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InviteTeamMember implements InvitesTeamMembers
{
    /**
     * Invite a new team member to the given team.
     */
    public function invite(User $user, Team $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        InvitingTeamMember::dispatch($team, $email, $role);

        $invitation = $team->teamInvitations()->create([
            'email' => $email,
            'role' => $role,
        ]);

        Mail::to($email)->send(new TeamInvitation($invitation));
    }

    /**
     * Validate the invite member operation.
     */
    protected function validate(Team $team, string $email, ?string $role): void
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules($team), [
            'email.unique' => __('This user has already been invited to the team.'),
        ])->after(function ($validator) use ($team, $email) {
            $this->ensureUserIsNotAlreadyOnTeam($team, $email)($validator);
            $this->ensureUserMeetsCustomCriteria($email)($validator);
        })->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for inviting a team member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(Team $team): array
    {
        return array_filter([
            'email' => [
                'required',
                'email',
                Rule::unique(Jetstream::plugin()->teamInvitationModel())->where(function (Builder $query) use ($team) {
                    $query->where('team_id', $team->id);
                }),
            ],
            'role' => Jetstream::plugin()->hasTeamsFeatures() ? ['required', 'string', new Role] : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
     */
    protected function ensureUserIsNotAlreadyOnTeam(Team $team, string $email): Closure
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the team.')
            );
        };
    }

    /**
     * Override this method in your application to add custom validation logic.
     * Example: Restrict invitations to specific user types, check subscription status, etc.
     */
    protected function ensureUserMeetsCustomCriteria(string $email): Closure
    {
        return function ($validator) {
            // Add your custom validation logic here
            // Example from HCareMatters:
            // $user = User::where('email', $email)->first();
            // if ($user && $user->user_type_id !== 2) {
            //     $validator->errors()->add('email', 'Only healthcare providers can be invited.');
            // }
        };
    }
}
