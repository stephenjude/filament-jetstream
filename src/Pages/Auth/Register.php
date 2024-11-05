<?php

namespace Filament\Jetstream\Pages\Auth;

use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Team;
use Illuminate\Database\Eloquent\Model;

class Register extends \Filament\Pages\Auth\Register
{
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        return $user;
    }
}
