<?php

namespace Filament\Jetstream;

use Closure;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\Request;

class ForceTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->is('*/logout')
            || $request->is('*/profile')
            || Jetstream::plugin()?->forceTwoFactorAuthetication() === false) {
            return $next($request);
        }

        /** @var FilamentUser $user */
        $user = filament()->auth()->user();

        if (! $user?->hasEnabledTwoFactorAuthentication()) {
            return redirect()->to(filament()->getCurrentPanel()->getProfileUrl());
        }

        return $next($request);
    }

    protected function redirectTo(): ?string
    {
        return filament()->getCurrentPanel()->getProfileUrl();
    }
}
