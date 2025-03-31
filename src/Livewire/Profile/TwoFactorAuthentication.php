<?php

namespace Filament\Jetstream\Livewire\Profile;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Jetstream\Actions\GenerateNewRecoveryCodes;
use Filament\Jetstream\Actions\RecoveryCode;
use Filament\Jetstream\Contracts\TwoFactorAuthenticationProvider;
use Filament\Jetstream\Events\RecoveryCodesGenerated;
use Filament\Jetstream\Events\TwoFactorAuthenticationConfirmed;
use Filament\Jetstream\Events\TwoFactorAuthenticationDisabled;
use Filament\Jetstream\Events\TwoFactorAuthenticationEnabled;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthentication extends BaseLivewireComponent
{
    public ?array $data = [];

    public bool $aside = true;

    public bool $isConfirmingSetup = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render()
    {
        return view('filament-jetstream::livewire.profile.two-factor-authentication');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('setup_key')
                    ->visible(fn () => $this->isConfirmingSetup)
                    ->label(fn () => __('Setup Key: :setup_key', [
                        'setup_key' => decrypt($this->authUser()->two_factor_secret),
                    ])),
                TextInput::make('code')
                    ->visible(fn () => $this->isConfirmingSetup)
                    ->label(__('filament-jetstream::default.form.code.label'))
                    ->required(),
                Actions::make([
                    Actions\Action::make('enableTwoFactorAuthentication')
                        ->label(__('filament-jetstream::default.action.enable.label'))
                        ->visible(fn () => !$this->isConfirmingSetup && ! $this->authUser()->hasEnabledTwoFactorAuthentication())
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
                        ->action(fn () => $this->enableTwoFactorAuthentication()),
                    Actions\Action::make('confirmSetup')
                        ->label(__('filament-jetstream::default.action.confirm.label'))
                        ->visible(fn () => $this->isConfirmingSetup)
                        ->submit('confirmTwoFactorAuthenticationSetup'),
                    Actions\Action::make('cancelSetup')
                        ->label(__('filament-jetstream::default.action.cancel.label'))
                        ->outlined()
                        ->visible(fn () => $this->isConfirmingSetup)
                        ->action(fn () => $this->disableTwoFactorAuthentication()),
                    Actions\Action::make('generateNewRecoveryCodes')
                        ->label(__('filament-jetstream::default.action.two_factor_authentication.label.regenerate_recovery_codes'))
                        ->outlined()
                        ->visible(fn () => $this->authUser()->hasEnabledTwoFactorAuthentication())
                        ->requiresConfirmation()
                        ->action(fn () => $this->regenerateNewRecoveryCodes()),
                    Actions\Action::make('disableTwoFactorAuthentication')
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
                                ->currentPassword(),
                        ])
                        ->action(fn () => $this->disableTwoFactorAuthentication()),
                ]),
            ])
            ->statePath('data');
    }

    public function confirmTwoFactorAuthenticationSetup(): void
    {
        try {
            $this->rateLimit(5);

            $data = $this->form->getState();

            $provider = app(TwoFactorAuthenticationProvider::class);

            $code = $data['code'];

            if (empty($user->two_factor_secret) ||
                empty($code) ||
                ! $provider->verify(decrypt($user->two_factor_secret), $code)) {
                throw ValidationException::withMessages([
                    'data.code' => __('filament-jetstream::default.form.code.error_message'),
                ]);
            }

            $user->forceFill(['two_factor_confirmed_at' => now()])->save();

            TwoFactorAuthenticationConfirmed::dispatch($user);

            $this->isConfirmingSetup = false;
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }
    }

    protected function enableTwoFactorAuthentication(): void
    {
        try {
            $this->rateLimit(5);

            $user = $this->authUser();

            if (! $user->two_factor_secret) {
                $provider = app(TwoFactorAuthenticationProvider::class);

                $user->forceFill([
                    'two_factor_secret' => encrypt($provider->generateSecretKey()),
                    'two_factor_recovery_codes' => encrypt(
                        json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())
                    ),
                ])->save();

                TwoFactorAuthenticationEnabled::dispatch($user);
            }

            $this->isConfirmingSetup = true;
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }
    }

    protected function disableTwoFactorAuthentication(): void
    {
        $user = $this->authUser();

        if (! is_null($user->two_factor_secret) ||
            ! is_null($user->two_factor_recovery_codes) ||
            ! is_null($user->two_factor_confirmed_at)) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            TwoFactorAuthenticationDisabled::dispatch($user);
        }
    }

    protected function regenerateNewRecoveryCodes(): void
    {
        $user = $this->authUser();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
