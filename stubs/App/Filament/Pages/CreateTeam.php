<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('Create Team');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->translateLabel(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        return app(\App\Actions\Jetstream\CreateTeam::class)->create(auth()->user(), $data);
    }
}
