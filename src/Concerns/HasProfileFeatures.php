<?php

namespace Filament\Jetstream\Concerns;

use Closure;
use Filament\Jetstream\Pages\Auth\Challenge;
use Filament\Jetstream\Pages\Auth\Recovery;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;

trait HasProfileFeatures
{
    public Closure | bool $updateProfileInformation = true;

    public Closure | bool $updateProfilePhoto = true;

    public string $profilePhotoDisk = 'public';

    public Closure | bool $updatePassword = true;

    public Closure | Password | null $passwordRule = null;

    protected Closure | bool $forceTwoFactorAuthentication = false;

    protected Closure | bool $twoFactorAuthentication = true;

    public Closure | bool $logoutOtherBrowserSessions = true;

    public Closure | bool $deleteAccount = true;

    public function profilePhoto(Closure | bool $condition = true, string $disk = 'public'): static
    {
        $this->updateProfilePhoto = $condition;

        $this->profilePhotoDisk = $disk;

        return $this;
    }

    public function profilePhotoDisk(): bool
    {
        return $this->evaluate($this->profilePhotoDisk) === true;
    }

    public function managesProfilePhotos(): bool
    {
        return $this->evaluate($this->updateProfilePhoto) === true;
    }

    public function enabledProfileInformationUpdate(): bool
    {
        return $this->evaluate($this->updateProfileInformation) === true;
    }

    public function profileInformation(Closure | bool $condition = true): static
    {
        $this->updateProfileInformation = $condition;

        return $this;
    }

    public function enabledPasswordUpdate(): bool
    {
        return $this->evaluate($this->updatePassword) === true;
    }

    public function passwordRule(): Password
    {
        return $this->evaluate($this->passwordRule) ?? Password::default();
    }

    public function updatePassword(Closure | bool $condition = true, ?Password $rule = null): static
    {
        $this->updatePassword = $condition;

        $this->passwordRule = $rule;

        return $this;
    }

    public function twoFactorAuthentication(bool | Closure $condition = true, bool | Closure $forced = false): static
    {
        $this->twoFactorAuthentication = $condition;

        $this->forceTwoFactorAuthentication = $forced;

        return $this;
    }

    public function enabledTwoFactorAuthetication(): bool
    {
        return $this->evaluate($this->twoFactorAuthentication) === true;
    }

    public function forceTwoFactorAuthetication(): bool
    {
        return $this->evaluate($this->forceTwoFactorAuthentication) === true;
    }

    public function twoFactorAuthenticationRoutes(): array
    {
        return [
            Route::get('/two-factor-challenge', Challenge::class)->name('two-factor.challenge'),
            Route::get('/two-factor-recovery', Recovery::class)->name('two-factor.recovery'),
        ];
    }

    public function enabledLogoutOtherBrowserSessions(): bool
    {
        return $this->evaluate($this->logoutOtherBrowserSessions) === true;
    }

    public function logoutBrowserSessions(Closure | bool $condition = true): static
    {
        $this->logoutOtherBrowserSessions = $condition;

        return $this;
    }

    public function enabledDeleteAccount(): bool
    {
        return $this->evaluate($this->deleteAccount) === true;
    }

    public function deleteAccount(Closure | bool $condition = true): static
    {
        $this->deleteAccount = $condition;

        return $this;
    }
}
