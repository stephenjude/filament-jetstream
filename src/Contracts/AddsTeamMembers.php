<?php

namespace Filament\Jetstream\Contracts;

interface AddsTeamMembers
{
    /**
     * Add a new team member to the given team.
     */
    public function add($user, $team, string $email, ?string $role = null): void;
}
