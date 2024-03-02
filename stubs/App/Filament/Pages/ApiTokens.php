<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
class ApiTokens extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static string $view = 'filament.pages.api-tokens';

    protected static ?string $navigationLabel = 'API Tokens';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool)config('filament-jetstream.navigation_items.api_tokens.display');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-jetstream.navigation_items.api_tokens.sort');
    }
}
