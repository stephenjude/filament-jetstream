<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Filament\Jetstream\Events\AddingTeamMember;
use Filament\Jetstream\Events\TeamMemberAdded;
use Filament\Jetstream\Jetstream;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcceptTeamInvitation
{
    /**
     * Accept a team invitation.
     */
    public function accept(string $invitationId): RedirectResponse
    {
        $invitation = $this->findInvitationOrFail($invitationId);

        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            $this->addExistingUserToTeam($user, $invitation);
            $this->cleanupInvitation($invitation);

            return $this->redirectAfterAcceptance($user, $invitation->team);
        } else {
            // Handle unregistered user
            return $this->handleUnregisteredUserInvitation($invitation);
        }
    }

    /**
     * Find the invitation or fail.
     */
    protected function findInvitationOrFail(string $invitationId)
    {
        $model = Jetstream::plugin()->teamInvitationModel();

        return $model::whereKey($invitationId)->with('team')->firstOrFail();
    }

    /**
     * Add an existing user to the team.
     */
    protected function addExistingUserToTeam(User $user, $invitation): void
    {
        if ($invitation->team->hasUserWithEmail($user->email)) {
            abort(403, __('This user already belongs to the team.'));
        }

        AddingTeamMember::dispatch($invitation->team, $user);

        $invitation->team->users()->attach($user, ['role' => $invitation->role]);

        $user->switchTeam($invitation->team);

        TeamMemberAdded::dispatch($invitation->team, $user);
    }

    /**
     * Handle invitation for unregistered users.
     */
    protected function handleUnregisteredUserInvitation($invitation): RedirectResponse
    {
        // Store invitation ID in session for after registration
        session(['pending_team_invitation' => $invitation->id]);

        // Check if registration is enabled (you may need to adjust this check based on your setup)
        if (config('fortify.features') && in_array('registration', config('fortify.features'))) {
            // Redirect to registration with message
            session()->flash('status', 'Please create an account first to accept this team invitation.');

            return redirect()->route('register');
        }

        // If registration is not enabled, create user automatically as fallback
        // You may want to remove this fallback and show an error instead
        $user = $this->createUserFromInvitation($invitation);
        $this->cleanupInvitation($invitation);

        return $this->redirectAfterAcceptance($user, $invitation->team);
    }

    /**
     * Create a new user from the invitation (only when registration is disabled).
     */
    protected function createUserFromInvitation($invitation): User
    {
        $userData = $this->getNewUserData($invitation);

        $user = User::create($userData);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        AddingTeamMember::dispatch($invitation->team, $user);

        $invitation->team->users()->attach($user, ['role' => $invitation->role]);

        $user->switchTeam($invitation->team);

        TeamMemberAdded::dispatch($invitation->team, $user);

        return $user;
    }

    /**
     * Override this method to customize new user creation.
     * Example: Set specific user_type_id, default values, etc.
     */
    protected function getNewUserData($invitation): array
    {
        return [
            'name' => $this->extractNameFromEmail($invitation->email),
            'email' => $invitation->email,
            'password' => Hash::make(Str::password()),
            // Add custom fields here:
        ];
    }

    /**
     * Extract a name from the email address.
     */
    protected function extractNameFromEmail(string $email): string
    {
        return ucfirst(explode('@', $email)[0]);
    }

    /**
     * Clean up the invitation after acceptance.
     */
    protected function cleanupInvitation($invitation): void
    {
        $invitation->delete();
    }

    /**
     * Redirect after successful acceptance.
     * Override this method to customize redirect behavior.
     */
    protected function redirectAfterAcceptance(User $user, Team $team): RedirectResponse
    {
        // Example: Redirect to specific dashboard based on user type
        return redirect()->intended(config('fortify.home', '/dashboard'));
    }

    /**
     * Process pending team invitation after user registration.
     * Call this method after successful user registration.
     */
    public static function processPendingInvitation(User $user): ?RedirectResponse
    {
        $invitationId = session('pending_team_invitation');

        if (! $invitationId) {
            return null;
        }

        // Clear the session
        session()->forget('pending_team_invitation');

        // Find the invitation
        $model = Jetstream::plugin()->teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->with('team')->first();

        if (! $invitation || $invitation->email !== $user->email) {
            return null;
        }

        // Process the invitation
        $acceptor = new static;
        $acceptor->addExistingUserToTeam($user, $invitation);
        $acceptor->cleanupInvitation($invitation);

        // Flash success message
        session()->flash('status', 'You have successfully joined the ' . $invitation->team->name . ' team!');

        return $acceptor->redirectAfterAcceptance($user, $invitation->team);
    }
}
