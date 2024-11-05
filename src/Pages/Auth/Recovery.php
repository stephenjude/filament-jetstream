<?php

namespace Filament\Jetstream\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\LoginResponse;

class Recovery extends BaseSimplePage
{
    protected static string $view = 'filament-jetstream::pages.auth.recovery';

    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $this->form->fill();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);

            $this->form->getState();

            Filament::auth()->loginUsingId(session('login.id'), session('login.remember'));

            session()->forget(['login.id', 'login.remember']);

            session()->regenerate();

            return app(LoginResponse::class);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }
    }

    public function challengeAction(): Action
    {
        return Action::make('two_factor_challenge_login')
            ->link()
            ->label(__('use an authentication code'))
            ->url(filament()->getCurrentPanel()->route('two-factor.challenge'));
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('recovery_code')
                ->hiddenLabel()
                ->hint(__('Please confirm access to your account by entering one of your emergency recovery codes.'))
                ->label(__('Recovery Code'))
                ->required()
                ->autocomplete()
                ->autofocus()->rules([
                    fn () => function (string $attribute, $value, $fail) {
                        $model = Filament::auth()->getProvider()->getModel();

                        if (! $user = $model::find(session('login.id'))) {
                            $fail(__('The provided two factor recovery code was invalid.'));

                            redirect()->to(filament()->getCurrentPanel()->getLoginUrl());

                            return;
                        }

                        $validCode = collect($user->recoveryCodes())->first(
                            fn ($code) => hash_equals($code, $value) ? $code : null
                        );

                        if (! $validCode) {
                            $fail(__('The provided two factor recovery code was invalid.'));
                        }
                    },
                ]),
            Actions::make([
                Actions\Action::make('authenticate')
                    ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
                    ->submit('authenticate'),
            ])->fullWidth(),
            Actions::make([
                Actions\Action::make('logout')
                    ->link()
                    ->label(__('Logout'))
                    ->action(function () {
                        Filament::auth()->logout();

                        redirect()->to(filament()->getCurrentPanel()->getLoginUrl());
                    }),
            ])->fullWidth(),
        ])
            ->statePath('data');
    }
}
