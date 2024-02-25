<?php

namespace FilamentJetstream\FilamentJetstream;

use Filament\Events\Auth\Registered;
use FilamentJetstream\FilamentJetstream\Commands\FilamentJetstreamCommand;
use FilamentJetstream\FilamentJetstream\Listeners\CreatePersonalTeam;
use FilamentJetstream\FilamentJetstream\Testing\TestsFilamentJetstream;
use Illuminate\Support\Facades\Event;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentJetstreamServiceProvider extends PackageServiceProvider
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
            ->hasCommands($this->getCommands());

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
    }

    public function packageBooted(): void
    {
        Event::listen(
            Registered::class,
            CreatePersonalTeam::class,
        );

        // Testing
        Testable::mixin(new TestsFilamentJetstream());
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentJetstreamCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }
}
