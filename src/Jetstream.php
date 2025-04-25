<?php

namespace Filament\Jetstream;

use Filament\Panel;

class Jetstream
{
    public static function getForeignKeyColumn(string $class)
    {
        return str($class)->classBasename()->snake()->append('_id')->toString();
    }

    public static function plugin(): JetstreamPlugin
    {
        return static::panel()
            ->getPlugin('filament-jetstream');
    }

    public static function panel(): Panel
    {
        return filament()
            ->getPanel(config('filament-jetstream.panel'));
    }
}
