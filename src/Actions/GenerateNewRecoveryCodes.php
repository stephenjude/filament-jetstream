<?php

namespace Filament\Jetstream\Actions;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Collection;
use Filament\Jetstream\Events\RecoveryCodesGenerated;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     */
    public function __invoke(FilamentUser $user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(
                json_encode(
                    Collection::times(8, function () {
                        return RecoveryCode::generate();
                    })->all()
                )
            ),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
