<?php

namespace FilamentJetstream\FilamentJetstream\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class FilamentJetstreamCommand extends Command
{
    public $signature = 'filament:jetstream:install {--teams : Indicates if team support should be installed}
                                              {--api : Indicates if API support should be installed}';

    public $description = 'Install the Jetstream and filament components.';

    public function handle(): int
    {
        $this->info('Filament Jetstream scaffolding...');

        $this->call('jetstream:install', [
            'stack' => 'livewire',
            '--verification' => true,
            '--dark' => true,
            '--teams' => $this->option('teams'),
            '--api' => $this->option('api')
        ]);

        $this->configureUser();

        $this->configureEmailVerification();

        if ($this->option('teams')) {
            $this->configureTeam();
        }

        $this->configureRoute();

        $this->configurePanel();

        $this->configureAssets();

        $this->line('');

        $this->info('Filament Jetstream scaffolding installed successfully.');

        return self::SUCCESS;
    }

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

    protected function configureRoute()
    {
        $this->replaceInFile(
            search: <<<'HEREDOC'
            Route::middleware([
                'auth:sanctum',
                config('jetstream.auth_session'),
                'verified',
            ])->group(function () {
                Route::get('/dashboard', function () {
                    return view('dashboard');
                })->name('dashboard');
            });
            HEREDOC,
            replace: '',
            path: base_path('routes/web.php')
        );
    }

    protected function configurePanel()
    {
        if (!empty(Filament::getPanels())) {
            (new Filesystem)->deleteDirectory(app_path('Providers/Filament'));
        }

        $this->callSilently('make:filament-panel', ['id' => 'app', '--force' => true]);

        $this->replaceInFile(
            search: '->login()',
            replace: <<<'HEREDOC'
                ->default()
                ->plugin(
                    \FilamentJetstream\FilamentJetstream\FilamentJetstreamPlugin::make()
                );
                HEREDOC,
            path: app_path('Providers/Filament/AppPanelProvider.php')
        );
    }


    protected function configureAssets(): void
    {
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

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    protected function runCommands($commands)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }
}
