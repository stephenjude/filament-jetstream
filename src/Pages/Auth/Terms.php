<?php

namespace Filament\Jetstream\Pages\Auth;

use Illuminate\Contracts\Support\Htmlable;

class Terms extends BaseSimplePage
{
    protected static string $view = 'filament-jetstream::pages.auth.terms';

    public ?array $data = [];

    public function getTitle(): string | Htmlable
    {
        return __('Terms of Service');
    }
}
