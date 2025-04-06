<?php

namespace Filament\Jetstream;

class Jetstream
{
    public static function userModel(): string
    {
        return config('filament-jetstream.models.user');
    }

    public static function teamModel(): string
    {
        return config('filament-jetstream.models.team');
    }

    public static function membershipModel(): string
    {
        return config('filament-jetstream.models.membership');
    }

    public static function teamInvitationModel(): string
    {
        return config('filament-jetstream.models.invitation');
    }

    public static function plugin(): JetstreamPlugin
    {
        return JetstreamPlugin::get();
    }
}
