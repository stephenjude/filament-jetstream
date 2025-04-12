@php use Filament\Jetstream\Jetstream; @endphp
<x-filament-panels::page>
    @if (Jetstream::plugin()?->enabledProfileInformationUpdate())
        @livewire(Filament\Jetstream\Livewire\Profile\UpdateProfileInformation::class)
    @endif

    @if (Jetstream::plugin()?->enabledPasswordUpdate())
        @livewire(Filament\Jetstream\Livewire\Profile\UpdatePassword::class)
    @endif

    @if (Jetstream::plugin()?->enabledTwoFactorAuthetication())
        @livewire(Filament\Jetstream\Livewire\Profile\TwoFactorAuthentication::class)
    @endif

    @if (Jetstream::plugin()?->enabledLogoutOtherBrowserSessions())
        @livewire(Filament\Jetstream\Livewire\Profile\LogoutOtherBrowserSessions::class)
    @endif

    @if (Jetstream::plugin()?->enabledDeleteAccount())
        @livewire(Filament\Jetstream\Livewire\Profile\DeleteAccount::class)
    @endif
</x-filament-panels::page>
