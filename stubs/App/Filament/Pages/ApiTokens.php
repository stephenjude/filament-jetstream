<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class ApiTokens extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected string $view = 'filament.pages.api-tokens';

    protected static ?string $navigationLabel = 'API Tokens';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}
