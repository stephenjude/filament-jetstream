<?php

namespace FilamentJetstream\FilamentJetstream\Listeners;

use App\Models\Team;
use Filament\Events\Auth\Registered;

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

        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);

        $user->ownedTeams()->save($team);

        $user->switchTeam($team);
    }
}
