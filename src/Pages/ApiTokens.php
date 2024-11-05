<?php

namespace Filament\Jetstream\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Facades\Route;

class ApiTokens extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static bool $isDiscovered = false;

    protected static ?string $slug = 'api-tokens';

    protected static ?string $title = 'API Tokens';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static string $view = 'filament-jetstream::pages.api-tokens';

    public static function getRelativeRouteName(): string
    {
        return 'api-tokens';
    }

    public static function registerRoutes(Panel $panel): void
    {
        if (filled(static::getCluster())) {
            Route::name(static::prependClusterRouteBaseName(''))
                ->prefix(static::prependClusterSlug(''))
                ->group(fn () => static::routes($panel));

            return;
        }

        static::routes($panel);
    }

    public static function getRouteName(?string $panel = null): string
    {
        $panel = $panel ? Filament::getPanel($panel) : Filament::getCurrentPanel();

        return $panel->generateRouteName(static::getRelativeRouteName());
    }

}
