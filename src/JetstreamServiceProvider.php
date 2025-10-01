<?php

namespace Filament\Jetstream;

use Filament\Jetstream\Commands\InstallCommand;
use Filament\Jetstream\Livewire\ApiTokens\CreateApiToken;
use Filament\Jetstream\Livewire\ApiTokens\ManageApiTokens;
use Filament\Jetstream\Livewire\Profile\DeleteAccount;
use Filament\Jetstream\Livewire\Profile\LogoutOtherBrowserSessions;
use Filament\Jetstream\Livewire\Profile\UpdatePassword;
use Filament\Jetstream\Livewire\Profile\UpdateProfileInformation;
use Filament\Jetstream\Livewire\Teams\AddTeamMember;
use Filament\Jetstream\Livewire\Teams\DeleteTeam;
use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Livewire\Teams\TeamMembers;
use Filament\Jetstream\Livewire\Teams\UpdateTeamName;
use Filament\Jetstream\Pages\ApiTokens;
use Filament\Jetstream\Pages\EditProfile;
use Filament\Jetstream\Pages\EditTeam;
use Livewire\Livewire;
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
            ->hasViews()
            ->hasTranslations()
            ->hasConfigFile(static::$name)
            ->hasCommands([InstallCommand::class]);

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php' => database_path('migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php'),
        ], 'filament-jetstream-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_08_22_134103_create_teams_table.php' => database_path('migrations/2025_08_22_134103_create_teams_table.php'),
        ], 'filament-jetstream-team-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_10_01_165811_add_slug_column_in_teams_table.php' => database_path('migrations/2025_10_01_165811_add_slug_column_in_teams_table.php'),
        ], 'filament-jetstream-team-migrations');
    }

    public function packageBooted()
    {
        $this->registerLivewireComponents();
    }

    private function registerLivewireComponents(): void
    {
        /*
         * Profile Components
         */
        Livewire::component('filament-jetstream::pages.edit-profile', EditProfile::class);
        Livewire::component(
            'filament-jetstream::livewire.profile.update-profile-information',
            UpdateProfileInformation::class
        );
        Livewire::component('filament-jetstream::livewire.profile.update-password', UpdatePassword::class);
        Livewire::component(
            'filament-jetstream::livewire.profile.logout-other-browser-sessions',
            LogoutOtherBrowserSessions::class
        );
        Livewire::component('filament-jetstream::livewire.profile.delete-account', DeleteAccount::class);

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
        Livewire::component(
            'filament-jetstream::livewire.teams.pending-team-invitations',
            PendingTeamInvitations::class
        );
        Livewire::component('filament-jetstream::livewire.teams.delete-team', DeleteTeam::class);
    }
}
