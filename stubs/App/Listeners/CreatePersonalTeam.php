<?php

namespace App\Listeners;

use App\Models\Team;
use Filament\Auth\Events\Registered;
use Laravel\Jetstream\Features;

class CreatePersonalTeam
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->getUser();

        if (Features::hasTeamFeatures()) {
            $team = Team::forceCreate([
                'user_id' => $user->getAuthIdentifier(),
                'name' => explode(' ', ($user->name ?? 'Unknown'), 2)[0] . "'s Team",
                'personal_team' => true,
            ]);

            $user->ownedTeams()->save($team);

            $user->switchTeam($team);
        }
    }
}
