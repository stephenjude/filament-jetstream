<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Events\TwoFactorAuthenticationDisabled;

class DisableTwoFactorAuthentication
{
    /**
     * Disable two factor authentication for the user.
     */
    public function __invoke($user): void
    {
        if (! is_null($user->two_factor_secret) ||
            ! is_null($user->two_factor_recovery_codes) ||
            ! is_null($user->two_factor_confirmed_at)) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            TwoFactorAuthenticationDisabled::dispatch($user);
        }
    }
}
