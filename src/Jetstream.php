<?php

namespace Filament\Jetstream;

class Jetstream
{
    public static function getForeignKeyColumn(string $class)
    {
       return str($class)->classBasename()->snake()->append('_id')->toString();
    }
    public static function plugin(): JetstreamPlugin
    {
        return filament()
            ->getPanel(config('filament-jetstream.panel'))
            ->getPlugin('filament-jetstream');
    }
}
