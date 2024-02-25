<?php

namespace FilamentJetstream\FilamentJetstream;

use App\Filament\App\Pages\ApiTokens;
use App\Filament\App\Pages\EditProfile;
use App\Models\Team;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use FilamentJetstream\FilamentJetstream\Pages\CreateTeam;
use FilamentJetstream\FilamentJetstream\Pages\EditTeam;
use Laravel\Jetstream\Features;

class FilamentJetstreamPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-jetstream';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->viteTheme('resources/css/app.css')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(static fn () => Filament::getTenant()
                        ? url(EditProfile::getUrl())
                        : url('/app')),
            ]);

        if (Features::hasApiFeatures()) {
            $panel
                ->userMenuItems([
                    MenuItem::make()
                        ->label('API Tokens')
                        ->icon('heroicon-o-key')
                        ->url(static fn () => Filament::getTenant()
                            ? url(ApiTokens::getUrl())
                            : url('/app')),
                ]);
        }

        if (Features::hasTeamFeatures()) {
            $panel
                ->tenant(Team::class)
                ->tenantRegistration(CreateTeam::class)
                ->tenantProfile(EditTeam::class)->userMenuItems([
                    MenuItem::make()
                        ->label('Team Settings')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->url(static fn () => Filament::getTenant()
                            ? url(\App\Filament\App\Pages\EditTeam::getUrl())
                            : url('/app')),
                ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
