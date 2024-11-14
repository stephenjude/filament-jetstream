<?php

namespace Filament\Jetstream\Livewire\Teams;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Jetstream\Livewire\BaseLivewireComponent;

class UpdateTeamName extends BaseLivewireComponent
{
    public ?array $data = [];

    public function mount(): void
    {
        $data = Filament::getTenant()->only(['name']);

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-jetstream::default.update_team_name.section.title'))
                    ->aside()
                    ->description(__('filament-jetstream::default.update_team_name.section.description'))
                    ->schema([
                        Placeholder::make('team_owner')
                            ->label(__('filament-jetstream::default.form.team_owner.label'))
                            ->content(fn (): string => __(':name (:email)', [
                                'name' => $this->authUser()->name,
                                'email' => $this->authUser()->email,
                            ])),
                        TextInput::make('name')
                            ->label(__('filament-jetstream::default.form.team_name.label'))
                            ->string()
                            ->maxLength(255)
                            ->required(),
                        Actions::make([
                            Actions\Action::make('save')
                                ->label(__('filament-jetstream::default.action.save.label'))
                                ->submit('updateTeamName'),
                        ])->alignEnd(),
                    ]),
            ])
            ->statePath('data');
    }

    public function updateTeamName(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        Filament::getTenant()->forceFill([
            'name' => $data['name'],
        ])->save();

        $this->sendNotification();
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.update-team-name');
    }
}
