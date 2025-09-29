<?php

namespace App\Actions\Jetstream;

use Closure;
use Filament\Jetstream\Contracts\AddsTeamMembers;
use Filament\Jetstream\Events\AddingTeamMember;
use Filament\Jetstream\Events\TeamMemberAdded;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Rules\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class AddTeamMember implements AddsTeamMembers
{
    /**
     * Add a new team member to the given team.
     */
    public function add($user, $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        $newTeamMember = Jetstream::plugin()->userModel()::where('email', $email)->firstOrFail();

        AddingTeamMember::dispatch($team, $newTeamMember);

        $team->users()->attach($newTeamMember, ['role' => $role]);

        TeamMemberAdded::dispatch($team, $newTeamMember);
    }

    /**
     * Validate the add member operation.
     */
    protected function validate($team, string $email, ?string $role): void
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules(), [
            'email.exists' => __('We were unable to find a registered user with this email address.'),
        ])->after(function ($validator) use ($team, $email) {
            $this->ensureUserIsNotAlreadyOnTeam($team, $email)($validator);
            $this->ensureUserMeetsCustomCriteria($email)($validator);
        })->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for adding a team member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(): array
    {
        return array_filter([
            'email' => ['required', 'email', 'exists:users'],
            'role' => Jetstream::plugin()->hasTeamsFeatures() ? ['required', 'string', new Role] : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
     */
    protected function ensureUserIsNotAlreadyOnTeam($team, string $email): Closure
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
     * Example: Restrict additions to specific user types, check subscription status, etc.
     */
    protected function ensureUserMeetsCustomCriteria(string $email): Closure
    {
        return function ($validator) {
            // Add your custom validation logic here
        };
    }
}
