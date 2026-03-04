<?php

namespace Filament\Jetstream\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Facades\Route;

class ApiTokens extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static bool $isDiscovered = false;

    protected static ?string $slug = 'tokens';

    protected static ?string $title = 'API Tokens';

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-key';

    protected string $view = 'filament-jetstream::pages.api-tokens';

    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'tokens';
    }

    public static function registerRoutes(Panel $panel, $configuration = null): void
    {
        $registerRoutes = fn () => $configuration === null
            ? static::routes($panel)
            : static::routes($panel, $configuration);

        if (filled(static::getCluster())) {
            Route::name(static::prependClusterRouteBaseName($panel, ''))
                ->prefix(static::prependClusterSlug($panel, ''))
                ->group($registerRoutes);

            return;
        }

        $registerRoutes();
    }

    public static function getRouteName(string | Panel | null $panel = null): string
    {
        $panel = $panel ?? filament()->getCurrentOrDefaultPanel();

        return $panel->generateRouteName(static::getRelativeRouteName());
    }
}
