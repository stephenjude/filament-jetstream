<?php

namespace FilamentJetstream\FilamentJetstream\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class FilamentJetstreamCommand extends Command
{
    public $signature = 'filament:jetstream:install {--teams : Indicates if team support should be installed}
                                              {--api : Indicates if API support should be installed}';

    public $description = 'Install the Laravel Jetstream and Filament Panel components.';

    /**
     * The console command description.
     */
    public function handle(): int
    {
        $this->info('Filament Jetstream scaffolding...');

        if (!$this->installFilamentPackage() || !$this->installJetstreamPackage()) {
            return self::FAILURE;
        }

        $this->call('jetstream:install', [
            'stack' => 'livewire',
            '--verification' => true,
            '--dark' => true,
            '--teams' => $this->option('teams'),
            '--api' => $this->option('api'),
        ]);

        $this->configureUser();

        $this->configureEmailVerification();

        if ($this->option('teams')) {
            $this->configureTeam();
        }

        $this->configurePanel();

        $this->configureAssets();

        $this->line('');

        $this->info('Filament Jetstream scaffolding installed successfully.');

        return self::SUCCESS;
    }

    /**
     * Install Filament package.
     */
    protected function installFilamentPackage(): bool
    {
        if (!$this->hasComposerPackage('filament/filament')) {
            return $this->requireComposerPackages('filament/filament:^3.2');
        }

        return true;
    }

    /**
     * Install Laravel Jetstream package.
     */
    protected function installJetstreamPackage(): bool
    {
        if (!$this->hasComposerPackage('laravel/jetstream')) {
            return $this->requireComposerPackages('laravel/jetstream:^4.2|^5.0');
        }

        return true;
    }

    /**
     * Configure User model for Filament panel.
     */
    protected function configureUser(): void
    {
        $this->replaceInFile(
            search: 'use Laravel\Sanctum\HasApiTokens;',
            replace: <<<'HEREDOC'
            use Filament\Panel;
            use Filament\Models\Contracts\FilamentUser;
            use Laravel\Sanctum\HasApiTokens;
            HEREDOC,
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: 'class User extends Authenticatable',
            replace: 'class User extends Authenticatable implements FilamentUser',
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: '}',
            replace: <<<'HEREDOC'

                public function canAccessPanel(Panel $panel): bool
                {
                    return true;
                }
            }
            HEREDOC,
            path: app_path('Models/User.php'),
        );
    }

    /**
     * Configure User model for Laravel email verification.
     */
    protected function configureEmailVerification(): void
    {
        $this->replaceInFile(
            search: '// use Illuminate\Contracts\Auth\MustVerifyEmail;',
            replace: 'use Illuminate\Contracts\Auth\MustVerifyEmail;',
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: 'implements ',
            replace: 'implements MustVerifyEmail, ',
            path: app_path('Models/User.php'),
        );
    }

    /**
     * Configure User model for Filament panel team features.
     */
    protected function configureTeam(): void
    {
        $this->replaceInFile(
            search: 'use Laravel\Sanctum\HasApiTokens;',
            replace: <<<'HEREDOC'
            use Illuminate\Support\Collection;
            use Illuminate\Database\Eloquent\Model;
            use Filament\Models\Contracts\HasTenants;
            use Laravel\Sanctum\HasApiTokens;
            HEREDOC,
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: 'implements ',
            replace: 'implements HasTenants, ',
            path: app_path('Models/User.php'),
        );

        $this->replaceInFile(
            search: <<<'HEREDOC'
                }
            }
            HEREDOC,
            replace: <<<'HEREDOC'
                }

                public function getTenants(Panel $panel): Collection
                {
                    return $this->allTeams();
                }

                public function canAccessTenant(Model $tenant): bool
                {
                    return $this->belongsToTeam($tenant);
                }
            }
            HEREDOC,
            path: app_path('Models/User.php'),
        );
    }

    /**
     * Configure Filament panel for the Jetstream.
     */
    protected function configurePanel()
    {
        $filesystem = (new Filesystem);

        $filesystem->ensureDirectoryExists(app_path('Providers/Filament'));

        collect($filesystem->files(app_path('Providers/Filament')))
            ->map(fn(\SplFileInfo $fileInfo) => str($fileInfo->getFilename())
                ->before('.php')->prepend("App\Providers\Filament")->append('::class,')->toString())
            ->each(fn($value) => $this->replaceInFile(search: $value, replace: '', path: config_path('app.php')));

        $filesystem->copyDirectory(__DIR__.'/../../stubs/App', app_path('/'));

        $filesystem->copyDirectory(__DIR__.'/../../stubs/resources/views/filament', resource_path('views/filament'));

        copy(__DIR__.'/../../stubs/routes/web.php', base_path('routes/web.php'));


        ServiceProvider::addProviderToBootstrapFile('App\Providers\Filament\AppPanelProvider');
    }

    /**
     * Configure blade css and tailwind configurations.
     */
    protected function configureAssets(): void
    {
        $this->call('filament:install', ['--scaffold' => true]);

        $this->replaceInFile(
            search: file_get_contents(base_path('tailwind.config.js')),
            replace: <<<'HEREDOC'
            import preset from './vendor/filament/filament/tailwind.config.preset';

            export default {
                presets: [preset],
                content: [
                    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
                    './vendor/laravel/jetstream/**/*.blade.php',
                    './storage/framework/views/*.php',
                    './resources/views/**/*.blade.php',
                    './app/Filament/**/*.php',
                    './resources/views/filament/**/*.blade.php',
                    './vendor/filament/**/*.blade.php',
                ],
            };
            HEREDOC,
            path: base_path('tailwind.config.js'),
        );

        $this->replaceInFile(
            search: 'indigo',
            replace: 'gray',
            path: resource_path('views/components/checkbox.blade.php'),
        );

        $this->replaceInFile(
            search: 'indigo',
            replace: 'gray',
            path: resource_path('views/components/button.blade.php'),
        );

        $this->replaceInFile(
            search: 'indigo',
            replace: 'gray',
            path: resource_path('views/components/input.blade.php'),
        );

        $this->replaceInFile(
            search: 'indigo',
            replace: 'gray',
            path: resource_path('views/components/secondary-button.blade.php'),
        );

        (new Filesystem)->deleteDirectory(resource_path('views/auth'));
        (new Filesystem)->delete(resource_path('views/dashboard.blade.php'));
        (new Filesystem)->delete(resource_path('views/navigation-menu.blade.php'));
        (new Filesystem)->delete(base_path('tests/Features/AuthenticationTest.php'));
        (new Filesystem)->delete(base_path('tests/Features/EmailVerificationTest.php'));
        (new Filesystem)->delete(base_path('tests/Features/PasswordConfirmationTest.php'));
        (new Filesystem)->delete(base_path('tests/Features/PasswordResetTest.php'));
        (new Filesystem)->delete(base_path('tests/Features/RegistrationTest.php'));

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    /**
     * Replace a given string within a given file.
     */
    protected function replaceInFile(string $search, string $replace, string $path): void
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Run the given commands.
     */
    protected function runCommands(array $commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (\RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }

    /**
     * Determine if the given Composer package is installed.
     */
    protected function hasComposerPackage(string $package): bool
    {
        $packages = json_decode(file_get_contents(base_path('composer.json')), true);

        return array_key_exists($package, $packages['require'] ?? [])
            || array_key_exists($package, $packages['require-dev'] ?? []);
    }

    /**
     * Installs the given Composer Packages into the application.
     */
    protected function requireComposerPackages(array|string $packages): bool
    {
        $command = array_merge(
            ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        return !(new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Install the service provider in the application configuration file.
     */
    protected function installServiceProviderAfter(string $after, string $name): void
    {
        if (!Str::contains(
            $appConfig = file_get_contents(config_path('app.php')),
            'App\\Providers\\'.$name.'::class'
        )) {
            file_put_contents(
                config_path('app.php'),
                str_replace(
                    'App\\Providers\\'.$after.'::class,',
                    'App\\Providers\\'.$after.'::class,'.PHP_EOL.'        App\\Providers\\'.$name.'::class,',
                    $appConfig
                )
            );
        }
    }
}
