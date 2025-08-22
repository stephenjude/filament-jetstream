<?php

namespace Filament\Jetstream\Pages;

use Filament\Pages\Page;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = '/dashboard';

    public function getColumns(): int | array
    {
        return 1;
    }
}
