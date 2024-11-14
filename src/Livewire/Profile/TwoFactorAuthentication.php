<?php

namespace Filament\Jetstream\Livewire\Profile;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Jetstream\Actions\ConfirmTwoFactorAuthentication;
use Filament\Jetstream\Actions\DisableTwoFactorAuthentication;
use Filament\Jetstream\Actions\EnableTwoFactorAuthentication;
use Filament\Jetstream\Actions\GenerateNewRecoveryCodes;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Illuminate\Support\Facades\Hash;

class TwoFactorAuthentication extends BaseLivewireComponent
{
    public ?array $data = [];

    public bool $aside = true;

    public ?string $redirectTo = null;

    public bool $isConfirmingSetup = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render()
    {
        return view('filament-jetstream::livewire.profile.two-factor-authentication');
    }

    public function confirmSetup(): void
    {
        try {
            $this->rateLimit(5);

            $data = $this->form->getState();

            app(ConfirmTwoFactorAuthentication::class)($this->authUser(), $data['code']);

            $this->isConfirmingSetup = false;

            if ($this->redirectTo) {
                redirect()->to($this->redirectTo);
            }
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }
    }

    public function confirmSetupAction(): Action
    {
        return Action::make('confirmSetup')
            ->label(__('filament-jetstream::default.action.confirm.label'))
            ->visible(fn () => $this->isConfirmingSetup)
            ->submit('confirmSetup');
    }

    public function cancelSetupAction(): Action
    {
        return Action::make('cancelSetup')
            ->label(__('filament-jetstream::default.action.cancel.label'))
            ->outlined()
            ->visible(fn () => $this->isConfirmingSetup)
            ->action(function () {
                try {
                    $this->rateLimit(5);

                    app(DisableTwoFactorAuthentication::class)($this->authUser());

                    $this->isConfirmingSetup = false;
                } catch (TooManyRequestsException $exception) {
                    $this->sendRateLimitedNotification($exception);

                    return;
                }
            });
    }

    protected function enableTwoFactorAuthenticationAction(): Action
    {
        return Action::make('enableTwoFactorAuthentication')
            ->label(__('filament-jetstream::default.action.enable.label'))
            ->visible(fn () => ! $this->authUser()->hasEnabledTwoFactorAuthentication())
            ->modalWidth('md')
            ->modalSubmitActionLabel(__('filament-jetstream::default.action.confirm.label'))
            ->form([
                TextInput::make('confirmPassword')
                    ->label(__('filament-jetstream::default.form.confirm_password.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->autofocus()
                    ->autocomplete('confirm-password')
                    ->rules([
                        fn () => function (string $attribute, $value, $fail) {
                            if (! Hash::check($value, $this->authUser()->password)) {
                                $fail(__('filament-jetstream::default.form.password.error_message'));
                            }
                        },
                    ]),
            ])
            ->action(function () {
                try {
                    $this->rateLimit(5);

                    app(EnableTwoFactorAuthentication::class)($this->authUser());

                    $this->isConfirmingSetup = true;
                } catch (TooManyRequestsException $exception) {
                    $this->sendRateLimitedNotification($exception);

                    return;
                }
            });
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('setup_key')
                    ->label(fn () => __(
                        'Setup Key: :setup_key',
                        ['setup_key' => decrypt($this->authUser()->two_factor_secret)]
                    )),
                TextInput::make('code')
                    ->label(__('filament-jetstream::default.form.code.label'))
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function disableTwoFactorAuthenticationAction(): Action
    {
        return Action::make('disableTwoFactorAuthentication')
            ->label(__('filament-jetstream::default.action.disable.label'))
            ->color('danger')
            ->visible(fn () => $this->authUser()->hasEnabledTwoFactorAuthentication())
            ->modalWidth('md')
            ->modalSubmitActionLabel(__('filament-jetstream::default.action.confirm.label'))
            ->form([
                TextInput::make('currentPassword')
                    ->label(__('filament-jetstream::default.form.current_password.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->autocomplete('current-password')
                    ->rules([
                        fn () => function (string $attribute, $value, $fail) {
                            if (! Hash::check($value, $this->authUser()->password)) {
                                $fail(__('filament-jetstream::default.form.password.error_message'));
                            }
                        },
                    ]),
            ])
            ->action(fn () => app(DisableTwoFactorAuthentication::class)($this->authUser()));
    }

    protected function generateNewRecoveryCodesAction(): Action
    {
        return Action::make('generateNewRecoveryCodes')
            ->label(__('filament-jetstream::default.two_factor_authentication.label.generate_recovery_codes'))
            ->outlined()
            ->visible(fn () => $this->authUser()->hasEnabledTwoFactorAuthentication())
            ->requiresConfirmation()
            ->action(fn () => app(GenerateNewRecoveryCodes::class)($this->authUser()));
    }
}
