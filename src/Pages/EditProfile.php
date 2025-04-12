<?php

namespace Filament\Jetstream\Pages;

use Filament\Facades\Filament;
use Filament\Jetstream\Jetstream;

class EditProfile extends \Filament\Pages\Auth\EditProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament-jetstream::pages.edit-profile';

    protected static ?string $navigationLabel = 'Profile';

    public function mount(): void
    {
        parent::mount();

        if ($id = $this->getUser()?->currentTeam?->id) {
            once(fn() => Filament::setTenant(Jetstream::plugin()->teamModel::find($id)));
        }
    }

    public static function isSimple(): bool
    {
        return false;
    }
}
