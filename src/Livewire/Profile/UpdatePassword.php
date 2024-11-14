<?php

namespace Filament\Jetstream\Livewire\Profile;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdatePassword extends BaseLivewireComponent
{
    public ?array $data = [];

    public function mount(): void {}

    public function render()
    {
        return view('filament-jetstream::livewire.profile.update-password');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-jetstream::default.update_profile_information.section.title'))
                    ->aside()
                    ->description(__('filament-jetstream::default.update_profile_information.section.description'))
                    ->schema([
                        TextInput::make('currentPassword')
                            ->label(__('filament-jetstream::default.form.current_password.label'))
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required()
                            ->autocomplete('current-password')
                            ->rules([
                                fn () => function (string $attribute, $value, $fail) {
                                    if (! Hash::check($value, $this->authUser()->password)) {
                                        $fail('The provided password does not match your current password.');
                                    }
                                },
                            ]),
                        TextInput::make('password')
                            ->label(__('filament-jetstream::default.form.password.label'))
                            ->password()
                            ->rule(Jetstream::plugin()?->passwordRule())
                            ->required()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->label(__('filament-jetstream::default.form.confirm_password.label'))
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required()
                            ->visible(fn (Get $get): bool => filled($get('password')))
                            ->dehydrated(false),
                        Actions::make([
                            Actions\Action::make('save')
                                ->label(__('filament-jetstream::default.action.save.label'))
                                ->submit('updatePassword'),
                        ]),
                    ]),
            ])
            ->statePath('data')
            ->model($this->authUser());
    }

    public function updatePassword(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = Arr::only($this->form->getState(), 'password');

        $user = $this->authUser();

        $user->fill($data);

        if (! $user->isDirty('password')) {
            return;
        }

        $user->save();

        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put(['password_hash_' . Filament::getAuthGuard() => $data['password']]);
        }

        $this->data['password'] = null;
        $this->data['currentConfirmation'] = null;
        $this->data['passwordConfirmation'] = null;

        $this->sendNotification();
    }
}
