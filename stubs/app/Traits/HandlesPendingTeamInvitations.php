<?php

namespace App\Traits;

use App\Actions\Jetstream\AcceptTeamInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

trait HandlesPendingTeamInvitations
{
    /**
     * Handle any pending team invitations after user registration or login.
     */
    protected function handlePendingTeamInvitation(User $user): ?RedirectResponse
    {
        // Only process if AcceptTeamInvitation action exists
        if (! class_exists(AcceptTeamInvitation::class)) {
            return null;
        }

        return AcceptTeamInvitation::processPendingInvitation($user);
    }
}
