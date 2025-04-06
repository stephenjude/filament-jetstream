<?php

namespace Filament\Jetstream;

use Filament\Jetstream\Commands\InstallCommand;
use Filament\Jetstream\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Filament\Jetstream\Livewire\ApiTokens\CreateApiToken;
use Filament\Jetstream\Livewire\ApiTokens\ManageApiTokens;
use Filament\Jetstream\Livewire\Profile\DeleteAccount;
use Filament\Jetstream\Livewire\Profile\LogoutOtherBrowserSessions;
use Filament\Jetstream\Livewire\Profile\TwoFactorAuthentication;
use Filament\Jetstream\Livewire\Profile\UpdatePassword;
use Filament\Jetstream\Livewire\Profile\UpdateProfileInformation;
use Filament\Jetstream\Livewire\Teams\AddTeamMember;
use Filament\Jetstream\Livewire\Teams\DeleteTeam;
use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Livewire\Teams\TeamMembers;
use Filament\Jetstream\Livewire\Teams\UpdateTeamName;
use Filament\Jetstream\Pages\ApiTokens;
use Filament\Jetstream\Pages\Auth\Challenge;
use Filament\Jetstream\Pages\Auth\Login;
use Filament\Jetstream\Pages\Auth\Recovery;
use Filament\Jetstream\Pages\EditProfile;
use Filament\Jetstream\Pages\EditTeam;
use Illuminate\Contracts\Cache\Repository;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class JetstreamServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-jetstream';

    public static string $viewNamespace = 'filament-jetstream';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile('filament-jetstream');

        $this->publishes([
            __DIR__ . '/../database/migrations/0001_01_01_000000_create_users_table.php' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
        ], 'filament-jetstream-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/0001_01_01_000000_create_teams_table.php' => database_path('migrations/0001_01_01_000000_create_teams_table.php'),
        ], 'filament-jetstream-team-migrations');

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageBooted()
    {
        $this->registerLivewireComponents();
    }

    public function packageRegistered()
    {
        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, function ($app) {
            return new TwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '0001_01_01_000000_create_users_table.php',
            '0001_01_01_000000_create_teams_table.php',
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            InstallCommand::class,
        ];
    }

    private function registerLivewireComponents(): void
    {
        /*
         * Profile Components
         */
        Livewire::component('filament-jetstream::pages.edit-profile', EditProfile::class);
        Livewire::component('filament-jetstream::livewire.profile.update-profile-information', UpdateProfileInformation::class);
        Livewire::component('filament-jetstream::livewire.profile.update-password', UpdatePassword::class);
        Livewire::component('filament-jetstream::livewire.profile.logout-other-browser-sessions', LogoutOtherBrowserSessions::class);
        Livewire::component('filament-jetstream::livewire.profile.delete-account', DeleteAccount::class);
        Livewire::component('filament-jetstream::livewire.profile.two-factor-authentication', TwoFactorAuthentication::class);
        Livewire::component('filament-jetstream::pages.auth.challenge', Challenge::class);
        Livewire::component('filament-jetstream::pages.auth.recovery', Recovery::class);

        Livewire::component('filament-panels::pages.auth.login', Login::class);

        /*
         * Api Token Components
         */
        Livewire::component('filament-jetstream::pages.api-tokens', ApiTokens::class);
        Livewire::component('filament-jetstream::livewire.api-tokens.create-api-token', CreateApiToken::class);
        Livewire::component('filament-jetstream::livewire.api-tokens.manage-api-tokens', ManageApiTokens::class);

        /*
         * Teams Components
         */
        Livewire::component('filament-jetstream::pages.edit-teams', EditTeam::class);
        Livewire::component('filament-jetstream::livewire.teams.update-team-name', UpdateTeamName::class);
        Livewire::component('filament-jetstream::livewire.teams.add-team-member', AddTeamMember::class);
        Livewire::component('filament-jetstream::livewire.teams.team-members', TeamMembers::class);
        Livewire::component('filament-jetstream::livewire.teams.pending-team-invitations', PendingTeamInvitations::class);
        Livewire::component('filament-jetstream::livewire.teams.delete-team', DeleteTeam::class);
    }
}
