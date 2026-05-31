<?php

namespace Filament\Jetstream\Events;

use App\Models\Team;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class TeamEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The team instance.
     *
     * @var Team
     */
    public $team;

    /**
     * Create a new event instance.
     *
     * @param  Team  $team
     * @return void
     */
    public function __construct($team)
    {
        $this->team = $team;
    }
}
