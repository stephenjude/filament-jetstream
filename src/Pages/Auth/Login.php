<?php

namespace Filament\Jetstream\Pages\Auth;

use Arr;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Jetstream\Events\TwoFactorAuthenticationChallenged;

class Login extends \Filament\Pages\Auth\Login
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);

            $data = $this->form->getState();

            $user = $this->validateCredentials($data);

            if (! $user->hasEnabledTwoFactorAuthentication()) {
                return parent::authenticate();
            }

            session([
                'login.id' => $user->getKey(),
                'login.remember' => $data['remember'],
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return $this->getLoginChallengeReseponse();
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }
    }

    private function validateCredentials(array $data): FilamentUser
    {
        if (! Filament::auth()->validate($this->getCredentialsFromFormData($data))) {
            $this->throwFailureValidationException();
        }

        $model = Filament::auth()->getProvider()->getModel();

        $user = $model::where(Arr::only($data, 'email'))->first();

        if (! ($user instanceof FilamentUser)) {
            $this->throwFailureValidationException();
        }

        if (! $user->canAccessPanel(Filament::getCurrentPanel())) {
            $this->throwFailureValidationException();
        }

        return $user;
    }

    private function getLoginChallengeReseponse(): LoginResponse
    {
        return new class implements LoginResponse
        {
            public function toResponse($request)
            {
                return redirect()->to(
                    filament()->getCurrentPanel()->route(
                        'two-factor.challenge'
                    )
                );
            }
        };
    }
}
