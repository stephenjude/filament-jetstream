<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Contracts\TwoFactorAuthenticationProvider;
use Filament\Jetstream\Events\TwoFactorAuthenticationEnabled;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Collection;

class EnableTwoFactorAuthentication
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
     * Enable two factor authentication for the user.
     */
    public function __invoke(FilamentUser $user, bool $force = false): void
    {
        if (empty($user->two_factor_secret) || $force === true) {
            $user->forceFill([
                'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])->save();

            TwoFactorAuthenticationEnabled::dispatch($user);
        }
    }
}
