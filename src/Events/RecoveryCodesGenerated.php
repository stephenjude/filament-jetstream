<?php

namespace Filament\Jetstream\Events;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Events\Dispatchable;

class RecoveryCodesGenerated
{
    use Dispatchable;

    /**
     * The user instance.
     */
    public FilamentUser $user;

    /**
     * Create a new event instance.
     */
    public function __construct(FilamentUser $user)
    {
        $this->user = $user;
    }
}
