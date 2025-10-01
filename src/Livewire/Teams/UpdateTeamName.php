<?php

namespace Filament\Jetstream\Livewire\Teams;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

class UpdateTeamName extends BaseLivewireComponent
{
    public ?array $data = [];

    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;

        $this->form->fill($team->only(['name', 'slug']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-jetstream::default.form.team_name.label'))
                    ->string()
                    ->maxLength(255)
                    ->required(),
                TextInput::make('slug')
                    ->label(__('filament-jetstream::default.form.team_slug.label'))
                    ->string()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->required(),
                Actions::make([
                    Action::make('save')
                        ->label(__('filament-jetstream::default.action.save.label'))
                        ->action(fn() => $this->updateTeamName($this->team)),
                ])->alignEnd(),
            ])
            ->statePath('data');
    }

    public function updateTeamName(Team $team): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        $team->forceFill([
            'name' => $data['name'],
        ])->save();

        $this->sendNotification();
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.update-team-name');
    }
}
