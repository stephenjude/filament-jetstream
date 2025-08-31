<?php

namespace Filament\Jetstream;

use Filament\Jetstream\Models\Team;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;

trait InteractsWIthProfile
{
    use TwoFactorAuthenticatable;
    use HasProfilePhoto;

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }
}
