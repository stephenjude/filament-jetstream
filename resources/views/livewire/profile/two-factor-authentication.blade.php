<div>
    <x-filament::section :aside="$aside">
        <x-slot name="heading">
            {{__('filament-jetstream::default.two_factor_authentication.section.title')}}
        </x-slot>

        <x-slot name="description">
            {{__('filament-jetstream::default.two_factor_authentication.section.description')}}
        </x-slot>

        <div class="">
            @if($this->isConfirmingSetup)
                <h2 class="text-xl font-medium mb-4">
                    Finish enabling two factor authentication.
                </h2>

                <p class="text-sm mb-4">
                    When two factor authentication is enabled, you will be prompted for a secure, random token during
                    authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>

                <p class="text-sm font-semibold mb-4">
                    To finish enabling two factor authentication, scan the following QR code using your phone's
                    authenticator application or enter the setup key and provide the generated OTP code.
                </p>

                <div class="mb-4">
                    {!! $this->authUser()->twoFactorQrCodeSvg() !!}
                </div>
            @endif

            @if(!$this->isConfirmingSetup && !$this->authUser()->hasEnabledTwoFactorAuthentication())
                <h2 class="text-xl font-medium mb-4">
                    You have not enabled two factor authentication.
                </h2>

                <p class="text-sm mb-4">
                    When two factor authentication is enabled, you will be prompted for a secure, random token during
                    authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>
            @endif

            @if($this->authUser()->hasEnabledTwoFactorAuthentication())
                <h2 class="text-xl font-medium mb-4">You have enabled two factor authentication.</h2>

                <p class="text-sm mb-4">
                    Store these recovery codes in a secure password manager. They can be used to recover
                    access to your account if your two factor authentication device is lost.
                </p>

                <div class="mb-4 p-4 bg-gray-100 rounded-md">
                    @foreach($this->authUser()->recoveryCodes() as $code)
                        <p class="text-sm font-medium mb-2">{{$code}}</p>
                    @endforeach
                </div>
            @endif

            <form>
                {{ $this->form }}
            </form>
        </div>
    </x-filament::section>

    <x-filament-actions::modals/>

    {{-- Success is as dangerous as failure. --}}
</div>
