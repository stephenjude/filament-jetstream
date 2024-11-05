<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Contracts\TwoFactorAuthenticationProvider;
use Filament\Jetstream\Events\TwoFactorAuthenticationConfirmed;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    /**
     * The two factor authentication provider.
     */
    protected TwoFactorAuthenticationProvider $provider;

    /**
     * Create a new action instance.
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Confirm the two factor authentication configuration for the user.
     */
    public function __invoke(FilamentUser $user, string $code): void
    {
        if (empty($user->two_factor_secret) ||
            empty($code) ||
            ! $this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'data.code' => __('The provided two factor authentication code was invalid.'),
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
