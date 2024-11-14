<?php

namespace Filament\Jetstream\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class InstallCommand extends Command
{
    public $signature = 'filament:jetstream:install {--teams : Indicates if team support should be installed}
                                              {--api : Indicates if API support should be installed}';

    public $description = 'Install the Laravel Jetstream and Filament Panel components.';

    /**
     * The console command description.
     */
    public function handle(): int
    {
        // Publish...
        $this->callSilent('vendor:publish', ['--tag' => 'filament-jetstream-migrations', '--force' => true]);

        // Storage...
        $this->callSilent('storage:link');

        if (file_exists(resource_path('views/welcome.blade.php'))) {
            $this->replaceInFile("Route::has('login')", 'filament()->hasLogin()', resource_path('views/welcome.blade.php'));

            $this->replaceInFile("{{ route('login') }}", '{{ filament()->getLoginUrl() }}', resource_path('views/welcome.blade.php'));

            $this->replaceInFile("Route::has('register')", 'filament()->hasRegistration()', resource_path('views/welcome.blade.php'));

            $this->replaceInFile("{{ route('register') }}", '{{ filament()->getRegistrationUrl() }}', resource_path('views/welcome.blade.php'));

            $this->replaceInFile("{{ url('/dashboard') }}", '{{ filament()->getHomeUrl() }}', resource_path('views/welcome.blade.php'));
        }

        (new Filesystem)->ensureDirectoryExists(app_path('Providers/Filament'));

        // Factories...
        copy(__DIR__ . '/../../database/factories/UserFactory.php', base_path('database/factories/UserFactory.php'));

        // User Model...
        copy(__DIR__ . '/../../stubs/app/Models/User.php', app_path('Models/User.php'));

        // Default Filament Panel...
        copy(__DIR__ . '/../../stubs/app/Providers/AppPanelProvider.php', app_path('Providers/Filament/AppPanelProvider.php'));

        // Teams...
        if ($this->option('teams')) {
            // Implement \Filament\Models\Contracts\HasTenants contract in User Model...
            $this->replaceInFile('//, \Filament\Models\Contracts\HasTenants', ', \Filament\Models\Contracts\HasTenants', app_path('Models/User.php'));

            // Add \Filament\Jetstream\HasTeams trait to User Model...
            $this->replaceInFile('// use \Filament\Jetstream\HasTeams;', 'use \Filament\Jetstream\HasTeams;', app_path('Models/User.php'));

            // Add Teams feature to Filament Panel...
            $this->replaceInFile('// ->teams()', '->teams()', app_path('Providers/Filament/AppPanelProvider.php'));
        }

        // API Tokens...
        if ($this->option('api')) {
            // Add HasApiTokens trait to User Model...
            $this->replaceInFile('// use \Laravel\Sanctum\HasApiTokens;', 'use \Laravel\Sanctum\HasApiTokens;', app_path('Models/User.php'));

            // Add API token feature to Filament Panel...
            $this->replaceInFile('// ->apiTokens()', '->apiTokens()', app_path('Providers/Filament/AppPanelProvider.php'));
        }

        // Add Filament Default Panel to Service Provider...
        ServiceProvider::addProviderToBootstrapFile('App\Providers\Filament\AppPanelProvider');

        $this->cleanup();

        $this->info('DONE: Jetstream installed successfully.');

        if ($this->option('api')) {
            $this->comment('TODO: Run `php artisan install:api` to install Laravel Sanctum.');
        }

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

    public function cleanup()
    {
        $this->replaceInFile('// use \Filament\Jetstream\HasTeams;', '', app_path('Models/User.php'));

        $this->replaceInFile('// use \Laravel\Sanctum\HasApiTokens;', '', app_path('Models/User.php'));

        $this->replaceInFile('//, \Filament\Models\Contracts\HasTenants', '', app_path('Models/User.php'));
    }
}
