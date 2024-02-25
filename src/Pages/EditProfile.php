<?php

namespace FilamentJetstream\FilamentJetstream\Pages;

use Filament\Pages\Page;

class EditProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament-jetstream::pages.edit-profile';

    protected static ?string $navigationLabel = 'Profile';

    public static function shouldRegisterNavigation(): bool
    {
        return config('filament-jetstream.navigation_items.profile.display');;
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-jetstream.navigation_items.profile.sort');
    }
}
