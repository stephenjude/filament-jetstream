<?php

namespace FilamentJetstream\FilamentJetstream\Pages;

use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;


class CreateTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return "Create Team";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        return app(\App\Actions\Jetstream\CreateTeam::class)->create(auth()->user(), $data);
    }
}
