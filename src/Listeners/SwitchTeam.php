<?php

namespace Filament\Jetstream\Listeners;

use Filament\Events\TenantSet;
use Filament\Jetstream\Jetstream;

class SwitchTeam
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
        if (Jetstream::plugin()?->hasTeamsFeatures()) {
            $event->getUser()->switchTeam($event->getTenant());
        }
    }
}
