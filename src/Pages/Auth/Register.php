<?php

namespace Filament\Jetstream\Pages\Auth;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class Register extends \Filament\Auth\Pages\Register
{
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        if (Filament::hasTenancy()) {
            $user->switchTeam($user->ownedTeams()->create([
                'name' => "$user->name's Team",
                'personal_team' => true,
            ]));
        }

        return $user;
    }
}
