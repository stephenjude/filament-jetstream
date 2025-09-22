<?php

namespace Filament\Jetstream;

use Filament\Panel;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;

trait InteractsWIthProfile
{
    use HasProfilePhoto;
    use TwoFactorAuthenticatable;

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
