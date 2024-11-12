<?php

namespace Filament\Jetstream\Pages\Auth;

use Illuminate\Contracts\Support\Htmlable;

class Policy extends BaseSimplePage
{
    protected static string $view = 'filament-jetstream::pages.auth.policy';

    public ?array $data = [];

    public function getTitle(): string | Htmlable
    {
        return __('Privacy Policy');
    }
}
