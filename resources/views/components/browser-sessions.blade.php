<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="text-sm mb-4">
        {{ __('filament-jetstream.browser_sessions.section.notice') }}
    </div>

    @if (count($sessions) > 0)
        <div class="space-y-6">
            <!-- Other Browser Sessions -->
            @foreach ($sessions as $session)
                <div class="flex items-center">
                    <div>
                        @if ($session->agent->isDesktop())
                            <x-filament::icon icon="heroicon-o-computer-desktop" class="w-8 h-8 "/>
                        @else
                            <x-filament::icon icon="heroicon-o-device-phone-mobile" class="w-8 h-8 "/>
                        @endif
                    </div>

                    <div class="ms-3">
                        <div class="text-sm">
                            {{ $session->agent->platform() ?: __('filament-jetstream.browser_sessions.section.labels.unknown_device') }}
                            - {{ $session->agent->browser() ?: __('filament-jetstream.browser_sessions.section.labels.unknown_device') }}
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">
                                {{ $session->ip_address }},

                                @if ($session->is_current_device)
                                    <span
                                        class="font-semibold">{{ __('filament-jetstream.browser_sessions.section.labels.current_device') }}</span>
                                @else
                                    {{ __('filament-jetstream.browser_sessions.section.labels.last_active') }} {{ $session->last_active }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-dynamic-component>
