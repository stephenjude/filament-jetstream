<?php

namespace Filament\Jetstream\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Jetstream\Events\AddingTeam;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;

class CreateTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('Create Team');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = Filament::auth()->user();

        if ($user === null) {
            throw new Exception(__('The authenticated user object must be a filament auth model!'));
        }

        AddingTeam::dispatch($user);

        $user->switchTeam($team = $user->ownedTeams()->create([
            'name' => $data['name'],
            'personal_team' => ! $user->currentTeam,
        ]));

        return $team;
    }
}
