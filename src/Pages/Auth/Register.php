<?php

namespace Filament\Jetstream\Pages\Auth;

use Illuminate\Database\Eloquent\Model;

class Register extends \Filament\Pages\Auth\Register
{
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        return $user;
    }
}
