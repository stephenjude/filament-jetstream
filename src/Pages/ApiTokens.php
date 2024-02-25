<?php

namespace FilamentJetstream\FilamentJetstream\Pages;

use Filament\Pages\Page;

class ApiTokens extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static string $view = 'filament-jetstream::pages.api-tokens';

    protected static ?string $navigationLabel = 'API Tokens';

    public static function shouldRegisterNavigation(): bool
    {
        return config('filament-jetstream.navigation_items.api_token.display');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-jetstream.navigation_items.api_token.sort');
    }
}
