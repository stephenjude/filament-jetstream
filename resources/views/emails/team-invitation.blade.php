@component('mail::message')
{{ __('filament-jetstream::default.mail.team_invitation.message.invitation', ['team' => $invitation->team->name]) }}

@if (filament()->hasRegistration())
{{ __('filament-jetstream::default.mail.team_invitation.message.requirement') }}

@component('mail::button', ['url' => filament()->getRegistrationUrl()])
{{ __('filament-jetstream::default.mail.team_invitation.label.create_account') }}
@endcomponent

{{ __('filament-jetstream::default.mail.team_invitation.message.requirement') }}

@else
{{ __('filament-jetstream::default.mail.team_invitation.message.instruction') }}
@endif


@component('mail::button', ['url' => $acceptUrl])
{{ __('filament-jetstream::default.mail.team_invitation.label.accept_invitation') }}
@endcomponent

{{ __('filament-jetstream::default.mail.team_invitation.message.notice') }}
@endcomponent
