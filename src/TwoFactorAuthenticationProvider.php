<?php

namespace Filament\Jetstream;

use Filament\Jetstream\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationProvider implements TwoFactorAuthenticationProviderContract
{
    /**
     * The underlying library providing two factor authentication helper services.
     */
    protected Google2FA $engine;

    /**
     * The cache repository implementation.
     */
    protected ?Repository $cache;

    /**
     * Create a new two factor authentication provider instance.
     */
    public function __construct(Google2FA $engine, ?Repository $cache = null)
    {
        $this->engine = $engine;
        $this->cache = $cache;
    }

    /**
     * Generate a new secret key.
     */
    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    /**
     * Get the two factor authentication QR code URL.
     */
    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    /**
     * Verify the given code.
     */
    public function verify(string $secret, string $code): bool
    {
        if (is_int($customWindow = config('fortify-options.two-factor-authentication.window'))) {
            $this->engine->setWindow($customWindow);
        }

        $timestamp = $this->engine->verifyKeyNewer(
            $secret,
            $code,
            optional($this->cache)->get($key = 'fortify.2fa_codes.' . md5($code))
        );

        if ($timestamp !== false) {
            if ($timestamp === true) {
                $timestamp = $this->engine->getTimestamp();
            }

            optional($this->cache)->put($key, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

            return true;
        }

        return false;
    }
}
