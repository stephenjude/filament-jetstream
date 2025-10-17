@use('Filament\Jetstream\Jetstream')

<x-filament-panels::page>
    @if (Jetstream::plugin()?->enabledProfileInformationUpdate())
        @livewire(config('filament-jetstream.livewire_components.profile.update_information', \Filament\Jetstream\Livewire\Profile\UpdateProfileInformation::class))
    @endif

    @if (Jetstream::plugin()?->enabledPasswordUpdate())
        @livewire(config('filament-jetstream.livewire_components.profile.update_password', \Filament\Jetstream\Livewire\Profile\UpdatePassword::class))
    @endif

    @if (Jetstream::plugin()?->enabledTwoFactorAuthetication())
        @livewire(\Stephenjude\FilamentTwoFactorAuthentication\Livewire\TwoFactorAuthentication::class)
    @endif

    @if (Jetstream::plugin()?->enabledPasskeyAuthetication())
        @livewire(\Stephenjude\FilamentTwoFactorAuthentication\Livewire\PasskeyAuthentication::class)
    @endif

    @if (Jetstream::plugin()?->enabledLogoutOtherBrowserSessions())
        @livewire(config('filament-jetstream.livewire_components.profile.logout_other_sessions', \Filament\Jetstream\Livewire\Profile\LogoutOtherBrowserSessions::class))
    @endif

    @if (Jetstream::plugin()?->enabledDeleteAccount())
        @livewire(config('filament-jetstream.livewire_components.profile.delete_account', \Filament\Jetstream\Livewire\Profile\DeleteAccount::class))
    @endif
</x-filament-panels::page>
