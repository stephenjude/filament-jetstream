<?php

namespace Filament\Jetstream\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TeamInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Model $invitation) {}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $path = __('filament.:path.team-invitations.accept', [
            'path' => filament()->getId(),
        ]);

        $url = URL::signedRoute($path, [
            'invitation' => $this->invitation,
        ]);

        return $this->subject(__('filament-jetstream::default.mail.team_invitation.subject'))
            ->markdown('filament-jetstream::emails.team-invitation', [
                'acceptUrl' => $url,
                'teamName' => $this->invitation->team?->name,
            ]);
    }
}
