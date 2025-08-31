<?php

namespace Filament\Jetstream\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class InstallCommand extends Command
{
    public $signature = 'filament-jetstream:install {--teams : Indicates if team support should be installed}
                                              {--api : Indicates if API support should be installed}';

    public $description = 'Install the Laravel Jetstream and Filament Panel components.';

    /**
     * The console command description.
     */
    public function handle(): int
    {
        // Add Filament Default Panel to Service Provider...
        (new Filesystem)->ensureDirectoryExists(app_path('Providers/Filament'));
        ServiceProvider::addProviderToBootstrapFile('App\Providers\Filament\AppPanelProvider');

        if (file_exists(resource_path('views/welcome.blade.php'))) {
            $this->replaceInFile(
                "Route::has('login')",
                'filament()->hasLogin()',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "{{ route('login') }}",
                '{{ filament()->getLoginUrl() }}',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "Route::has('register')",
                'filament()->hasRegistration()',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "{{ route('register') }}",
                '{{ filament()->getRegistrationUrl() }}',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "{{ url('/dashboard') }}",
                '{{ filament()->getHomeUrl() }}',
                resource_path('views/welcome.blade.php')
            );
        }

        // Factories...
        copy(__DIR__ . '/../../database/factories/UserFactory.php', base_path('database/factories/UserFactory.php'));

        // User Model...
        copy(__DIR__ . '/../../stubs/app/Models/User.php', app_path('Models/User.php'));

        // Default Filament Panel...
        copy(
            __DIR__ . '/../../stubs/app/Providers/AppPanelProvider.php',
            app_path('Providers/Filament/AppPanelProvider.php')
        );

        // Setup Team
        if ($this->option('teams')) {
            $this->call('vendor:publish', ['--tag' => 'filament-jetstream-team-migrations']);

            // Factories
            copy(__DIR__ . '/../../database/factories/TeamFactory.php', base_path('database/factories/TeamFactory.php'));

            // Implement \Filament\Models\Contracts\HasTenants contract in User Model
            $this->replaceInFile(
                '// use Filament\Models\Contracts\HasTenants;',
                'use Filament\Models\Contracts\HasTenants;',
                app_path('Models/User.php')
            );

            $this->replaceInFile(', MustVerifyEmail', ', MustVerifyEmail, HasTenants', app_path('Models/User.php'));

            // Add \Filament\Jetstream\HasTeams trait to User Model
            $this->replaceInFile(
                '// use Filament\Jetstream\HasTeams',
                'use Filament\Jetstream\HasTeams',
                app_path('Models/User.php')
            );

            $this->replaceInFile(
                '// use HasTeams;',
                'use HasTeams;',
                app_path('Models/User.php')
            );

            // Add Teams features to Filament Panel
            $this->replaceInFile(
                '->twoFactorAuthentication()',
                '->twoFactorAuthentication()
                    ->teams()',
                app_path('Providers/Filament/AppPanelProvider.php')
            );
        }

        // API Tokens
        if ($this->option('api')) {
            // Add HasApiTokens trait to User Model...
            $this->replaceInFile(
                '// use Laravel\Sanctum\HasApiTokens;',
                'use Laravel\Sanctum\HasApiTokens;',
                app_path('Models/User.php')
            );

            $this->replaceInFile(
                '// use HasApiTokens;',
                'use HasApiTokens;',
                app_path('Models/User.php')
            );

            // Add API token feature to Filament Panel...
            $this->replaceInFile(
                '->twoFactorAuthentication()',
                '->twoFactorAuthentication()
                    ->apiTokens()',
                app_path('Providers/Filament/AppPanelProvider.php')
            );

            $this->call('install:api', ['--without-migration-prompt' => true]);
        }

        // Publish filament assets
        $this->call('filament:install', ['--scaffold' => true, '--notifications' => true]);

        // Publish passkey migrations
        $this->call('vendor:publish', ['--tag' => 'passkeys-migrations']);

        // Publish jetstream migrations
        $this->call('vendor:publish', ['--tag' => 'filament-jetstream-migrations']);

        // Publish 2FA migrations
        $this->call('vendor:publish', ['--tag' => 'filament-two-factor-authentication-migrations']);

        // Link local storage
        $this->call('storage:link');

        $this->info('DONE: Filament Jetstream starter kit installed successfully.');

        return self::SUCCESS;
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $replace
     * @param  string|array  $search
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
