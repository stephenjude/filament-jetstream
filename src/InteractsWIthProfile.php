<?php

namespace Filament\Jetstream;

use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;

trait InteractsWIthProfile
{
    use HasProfilePhoto;
    use TwoFactorAuthenticatable;

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }
}
