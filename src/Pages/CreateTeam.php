<?php

namespace Filament\Jetstream\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Jetstream\Events\AddingTeam;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('filament-jetstream::default.page.create_team.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
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

        $user->switchTeam(
            $team = $user->ownedTeams()->create([
                'name' => $data['name'],
                'personal_team' => ! $user->currentTeam,
            ])
        );

        return $team;
    }
}
