<?php

namespace App\Listeners;

use App\Models\Team;
use Filament\Events\Auth\Registered;
use Filament\Events\TenantSet;
use Laravel\Jetstream\Features;

class SetCurrentTeamId
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
    public function handle(TenantSet $event): void
    {
        if (Features::hasTeamFeatures()) {
            $user = $event->getUser();
            $user->switchTeam($event->getTenant());
        }
    }
}

