<?php

namespace Filament\Jetstream\Concerns;

use Closure;
use Illuminate\Validation\Rules\Password;

trait HasProfileFeatures
{
    public string $userModel = 'App\\Models\\User';

    public Closure | bool $updateProfileInformation = true;

    public Closure | bool $updateProfilePhoto = true;

    public string $profilePhotoDisk = 'public';

    public Closure | bool $updatePassword = true;

    public Closure | Password | null $passwordRule = null;

    protected Closure | bool $forceTwoFactorAuthentication = false;

    protected Closure | bool $enablePasskeyAuthentication = false;

    protected Closure | bool $requiresPasswordForAuthenticationSetup = false;

    protected Closure | bool $twoFactorAuthentication = true;

    public Closure | bool $logoutOtherBrowserSessions = true;

    public Closure | bool $deleteAccount = true;

    public function profilePhoto(Closure | bool $condition = true, string $disk = 'public'): static
    {
        $this->updateProfilePhoto = $condition;

        $this->profilePhotoDisk = $disk;

        return $this;
    }

    public function profilePhotoDisk(): ?string
    {
        return $this->evaluate($this->profilePhotoDisk);
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

    public function twoFactorAuthentication(
        bool | Closure $condition = true,
        bool | Closure $forced = false,
        bool | Closure $enablePasskey = true,
        bool | Closure $requiresPassword = true
    ): static {
        $this->twoFactorAuthentication = $condition;

        $this->forceTwoFactorAuthentication = $forced;

        $this->enablePasskeyAuthentication = $enablePasskey;

        $this->requiresPasswordForAuthenticationSetup = $requiresPassword;

        return $this;
    }

    public function enabledTwoFactorAuthetication(): bool
    {
        return $this->evaluate($this->twoFactorAuthentication) === true;
    }

    public function enabledPasskeyAuthetication(): bool
    {
        return $this->evaluate($this->enablePasskeyAuthentication) === true;
    }

    public function forceTwoFactorAuthetication(): bool
    {
        return $this->evaluate($this->forceTwoFactorAuthentication) === true;
    }

    public function requiresPasswordForAuthenticationSetup(): bool
    {
        return $this->evaluate($this->requiresPasswordForAuthenticationSetup) === true;
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

    public function configureUserModel(string $userModel = 'App\\Models\\User'): static
    {
        $this->userModel = $userModel;

        return $this;
    }

    public function userModel(): string
    {
        return $this->userModel;
    }
}
