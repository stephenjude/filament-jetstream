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
            ->hasTranslations('filament-jetstream')
            ->hasConfigFile(static::$name)
            ->hasCommands([InstallCommand::class]);

        $this->publishes([
            __DIR__.'/../database/migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php' => database_path('migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php'),
        ], 'filament-jetstream-migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/2025_08_22_134103_create_teams_table.php' => database_path('migrations/2025_08_22_134103_create_teams_table.php'),
        ], 'filament-jetstream-team-migrations');

        // Publish Livewire components
        $this->publishes([
            __DIR__.'/../Livewire' => app_path('Livewire/FilamentJetstream'),
        ], 'filament-jetstream-livewire');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/filament-jetstream'),
        ], 'filament-jetstream-views');

        // Publish translations
        $this->publishes([
            __DIR__.'/../resources/lang/en/default.php' => lang_path('en/filament-jetstream.php'),
        ], 'filament-jetstream-translations');

        // Publish pages
        $this->publishes([
            __DIR__.'/../Pages' => app_path('Filament/Pages/FilamentJetstream'),
        ], 'filament-jetstream-pages');
    }

    public function packageBooted()
    {
        $this->registerLivewireComponents();
    }

    private function registerLivewireComponents(): void
    {
        $config = config('filament-jetstream', []);

        /*
         * Profile Components
         */
        $editProfileClass = $config['pages']['edit_profile'] ?? EditProfile::class;
        Livewire::component('filament-jetstream::pages.edit-profile', $editProfileClass);

        $updateProfileInfoClass = $config['livewire_components']['profile']['update_information'] ?? UpdateProfileInformation::class;
        Livewire::component(
            'filament-jetstream::livewire.profile.update-profile-information',
            $updateProfileInfoClass
        );
        Livewire::component(
            'filament.jetstream.livewire.profile.update-profile-information',
            $updateProfileInfoClass
        );

        $updatePasswordClass = $config['livewire_components']['profile']['update_password'] ?? UpdatePassword::class;
        Livewire::component('filament-jetstream::livewire.profile.update-password', $updatePasswordClass);
        Livewire::component('filament.jetstream.livewire.profile.update-password', $updatePasswordClass);

        $logoutSessionsClass = $config['livewire_components']['profile']['logout_other_sessions'] ?? LogoutOtherBrowserSessions::class;
        Livewire::component(
            'filament-jetstream::livewire.profile.logout-other-browser-sessions',
            $logoutSessionsClass
        );
        Livewire::component(
            'filament.jetstream.livewire.profile.logout-other-browser-sessions',
            $logoutSessionsClass
        );

        $deleteAccountClass = $config['livewire_components']['profile']['delete_account'] ?? DeleteAccount::class;
        Livewire::component('filament-jetstream::livewire.profile.delete-account', $deleteAccountClass);
        Livewire::component('filament.jetstream.livewire.profile.delete-account', $deleteAccountClass);

        /*
         * Api Token Components
         */
        $apiTokensClass = $config['pages']['api_tokens'] ?? ApiTokens::class;
        Livewire::component('filament-jetstream::pages.api-tokens', $apiTokensClass);

        $createApiTokenClass = $config['livewire_components']['api_tokens']['create'] ?? CreateApiToken::class;
        Livewire::component('filament-jetstream::livewire.api-tokens.create-api-token', $createApiTokenClass);
        Livewire::component('filament.jetstream.livewire.api-tokens.create-api-token', $createApiTokenClass);

        $manageApiTokensClass = $config['livewire_components']['api_tokens']['manage'] ?? ManageApiTokens::class;
        Livewire::component('filament-jetstream::livewire.api-tokens.manage-api-tokens', $manageApiTokensClass);
        Livewire::component('filament.jetstream.livewire.api-tokens.manage-api-tokens', $manageApiTokensClass);

        /*
         * Teams Components
         */
        $editTeamClass = $config['pages']['edit_team'] ?? EditTeam::class;
        Livewire::component('filament-jetstream::pages.edit-teams', $editTeamClass);

        $updateTeamNameClass = $config['livewire_components']['teams']['update_team_name'] ?? UpdateTeamName::class;
        Livewire::component('filament-jetstream::livewire.teams.update-team-name', $updateTeamNameClass);
        Livewire::component('filament.jetstream.livewire.teams.update-team-name', $updateTeamNameClass);

        $addTeamMemberClass = $config['livewire_components']['teams']['add_member'] ?? AddTeamMember::class;
        Livewire::component('filament-jetstream::livewire.teams.add-team-member', $addTeamMemberClass);
        Livewire::component('filament.jetstream.livewire.teams.add-team-member', $addTeamMemberClass);

        $teamMembersClass = $config['livewire_components']['teams']['team_members'] ?? TeamMembers::class;
        Livewire::component('filament-jetstream::livewire.teams.team-members', $teamMembersClass);
        Livewire::component('filament.jetstream.livewire.teams.team-members', $teamMembersClass);

        $pendingInvitationsClass = $config['livewire_components']['teams']['pending_invitations'] ?? PendingTeamInvitations::class;
        Livewire::component(
            'filament-jetstream::livewire.teams.pending-team-invitations',
            $pendingInvitationsClass
        );
        Livewire::component(
            'filament.jetstream.livewire.teams.pending-team-invitations',
            $pendingInvitationsClass
        );

        $deleteTeamClass = $config['livewire_components']['teams']['delete_team'] ?? DeleteTeam::class;
        Livewire::component('filament-jetstream::livewire.teams.delete-team', $deleteTeamClass);
        Livewire::component('filament.jetstream.livewire.teams.delete-team', $deleteTeamClass);
    }
}
