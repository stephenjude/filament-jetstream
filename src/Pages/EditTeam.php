<?php

namespace FilamentJetstream\FilamentJetstream\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditTeam extends EditTenantProfile
{
    protected static string $view = 'filament-jetstream::pages.edit-team';

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return 'Team Settings';
    }

    protected function getViewData(): array
    {
        return [
            'team' => Filament::getTenant(),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('filament-jetstream.navigation_items.team.display');
    }

    public static function getNavigationSort(): ?int
    {
        return (bool) config('filament-jetstream.navigation_items.team.sort');
    }
}
