<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditTeam extends EditTenantProfile
{
    protected string $view = 'filament.pages.edit-team';

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return __('Team Settings');
    }

    protected function getViewData(): array
    {
        return [
            'team' => Filament::getTenant(),
        ];
    }
}
