<?php

namespace FilamentJetstream\FilamentJetstream\Commands;

use Illuminate\Console\Command;

class FilamentJetstreamCommand extends Command
{
    public $signature = 'filament:jetstream:install {--teams : Indicates if team support should be installed}
                                              {--api : Indicates if API support should be installed}
                                              {--verification : Indicates if email verification support should be installed}
                                              {--dark : Indicate that dark mode support should be installed}';

    public $description = 'Install the Jetstream component and it\'s filament component';

    public function handle(): int
    {
        $this->callSilently('jetstream:install', [
            'stack' => 'livewire',
            '--teams' => $this->option('teams'),
            '--api' => $this->option('api'),
            '--verification' => $this->option('verification'),
            '--dark' => $this->option('dark'),
        ]);

        $this->configureUser();

        if ($this->option('teams')) {
            $this->configureTeam();
        }

        $this->configureAssets();

        $this->line('');

        $this->info('Jetstream scaffolding installed successfully.');

        return self::SUCCESS;
    }

    public function configureUser(): void
    {
        $this->replaceInFile(
            search: 'use Laravel\Sanctum\HasApiTokens;',
            replace: 'use Filament\Panel;'.PHP_EOL.
            'use Filament\Models\Contracts\FilamentUser;'.PHP_EOL.
            'use Laravel\Sanctum\HasApiTokens;'.PHP_EOL,
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: 'class User extends Authenticatable',
            replace: 'class User extends Authenticatable implements FilamentUser',
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: '}'.PHP_EOL,
            replace: 'public function canAccessPanel(Panel $panel): bool'.PHP_EOL.'{'.PHP_EOL.
            '   return;'.PHP_EOL.'}'.PHP_EOL.'}'.PHP_EOL,
            path: app_path('Models/User.php'),
        );

        if ($this->option('verification')) {
            $this->replaceInFile(
                search: 'implements ',
                replace: 'implements MustVerifyEmail, ',
                path: app_path('Models/User.php'),
            );
        }
    }

    public function configureTeam(): void
    {
        $this->replaceInFile(
            search: 'use Laravel\Sanctum\HasApiTokens;',
            replace: 'use Illuminate\Support\Collection;'.PHP_EOL.
            'use Illuminate\Database\Eloquent\Model;'.PHP_EOL.
            'use Filament\Models\Contracts\HasTenants;'.PHP_EOL.
            'use Laravel\Sanctum\HasApiTokens;'.PHP_EOL,
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: 'implements ',
            replace: 'implements HasTenants, ',
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: '}'.PHP_EOL.'}'.PHP_EOL,
            replace: 'public function canAccessTenant(Model $tenant): bool'.PHP_EOL.'{'.PHP_EOL.
            '   return $this->belongsToTeam($tenant);'.PHP_EOL.'}'.PHP_EOL.'}'.PHP_EOL,
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: '}'.PHP_EOL.'}'.PHP_EOL,
            replace: 'public function getTenants(Panel $panel): Collection'.PHP_EOL.'{'.PHP_EOL.
            '   return $this->allTeams();'.PHP_EOL.'}'.PHP_EOL.'}'.PHP_EOL,
            path: app_path('Models/User.php'),
        );
    }

    public function configureAssets(): void
    {
        $this->replaceInFile(
            search: 'content: ['.PHP_EOL,
            replace: 'content: ['.PHP_EOL.
            './app/Filament/**/*.php'.PHP_EOL.
            './resources/views/filament/**/*.blade.php'.PHP_EOL.
            './vendor/filament/**/*.blade.php'.PHP_EOL,
            path: base_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: 'implements FilamentUser',
            replace: 'implements FilamentUser, HasTenants',
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: '}'.PHP_EOL.'}'.PHP_EOL,
            replace: 'public function canAccessTenant(Model $tenant): bool'.PHP_EOL.'{'.PHP_EOL.
            '   return $this->belongsToTeam($tenant);'.PHP_EOL.'}'.PHP_EOL.'}'.PHP_EOL,
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: '}'.PHP_EOL.'}'.PHP_EOL,
            replace: 'public function getTenants(Panel $panel): Collection'.PHP_EOL.'{'.PHP_EOL.
            '   return $this->allTeams();'.PHP_EOL.'}'.PHP_EOL.'}'.PHP_EOL,
            path: app_path('tailwind.config.js'),
        );
    }

    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
