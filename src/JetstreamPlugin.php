<?php

namespace Filament\Jetstream;

use Filament\Contracts\Plugin;
use Filament\Events\TenantSet;
use Filament\Jetstream\Concerns\HasApiFeatures;
use Filament\Jetstream\Concerns\HasProfileFeatures;
use Filament\Jetstream\Concerns\HasTeamsFeatures;
use Filament\Jetstream\Listeners\SwitchTeam;
use Filament\Jetstream\Pages\ApiTokens;
use Filament\Jetstream\Pages\Auth\Login as TwoFactorLogin;
use Filament\Jetstream\Pages\CreateTeam;
use Filament\Jetstream\Pages\EditProfile;
use Filament\Jetstream\Pages\EditTeam;
use Filament\Pages\Auth\Login;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Event;

class JetstreamPlugin implements Plugin
{
    use EvaluatesClosures;
    use HasApiFeatures;
    use HasProfileFeatures;
    use HasTeamsFeatures;

    public function getId(): string
    {
        return 'filament/jetstream';
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

    public function register(Panel $panel): void
    {

        $panel
            ->login($this->enabledTwoFactorAuthetication() ? TwoFactorLogin::class : Login::class)
            ->routes(fn () => $this->enabledTwoFactorAuthetication() ? $this->twoFactorAuthenticationRoutes() : [])
            ->profile(EditProfile::class)
            ->authMiddleware([
                ForceTwoFactorAuthentication::class,
            ]);

        if ($this->hasApiTokensFeatures()) {
            $panel
                ->pages([ApiTokens::class])
                ->userMenuItems([$this->apiTokenMenuItem($panel)]);
        }

        if ($this->hasTeamsFeatures()) {
            $panel
                ->tenant(\App\Models\Team::class)
                ->tenantRegistration(CreateTeam::class)
                ->tenantProfile(EditTeam::class)
                ->routes(fn () => $this->teamsRoutes());
        }
    }

    public function boot(Panel $panel): void
    {
        /**
         * Listen and switch team if tenant was changed
         */
        Event::listen(TenantSet::class, SwitchTeam::class);
    }
}
