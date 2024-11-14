<?php

namespace Filament\Jetstream\Events;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Queue\SerializesModels;

class RecoveryCodeReplaced
{
    use SerializesModels;

    /**
     * The authenticated user.
     */
    public FilamentUser $user;

    /**
     * The recovery code.
     */
    public string $code;

    /**
     * Create a new event instance.
     */
    public function __construct(FilamentUser $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }
}
