@component('mail::message')
{{ __('filament-jetstream.mail.team_invitation.message.invitation', ['team' => $teamName]) }}

{{ __('filament-jetstream.mail.team_invitation.message.instruction') }}

@component('mail::button', ['url' => $acceptUrl])
{{ __('filament-jetstream.mail.team_invitation.label.accept_invitation') }}
@endcomponent

{{ __('filament-jetstream.mail.team_invitation.message.notice') }}
@endcomponent
