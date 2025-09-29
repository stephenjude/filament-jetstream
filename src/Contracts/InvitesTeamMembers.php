<?php

namespace Filament\Jetstream\Contracts;

interface InvitesTeamMembers
{
    /**
     * Invite a new team member to the given team.
     */
    public function invite($user, $team, string $email, ?string $role = null): void;
}
