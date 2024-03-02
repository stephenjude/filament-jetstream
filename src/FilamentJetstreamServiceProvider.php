<?php

namespace FilamentJetstream\FilamentJetstream;

use FilamentJetstream\FilamentJetstream\Commands\FilamentJetstreamCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentJetstreamServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-jetstream';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands());
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
}
