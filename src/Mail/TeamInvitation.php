<?php

namespace Filament\Jetstream\Mail;

use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TeamInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The team invitation instance.
     */
    public TeamInvitationModel $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(TeamInvitationModel $invitation)
    {
        $this->invitation = $invitation;
    }

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

        return $this->markdown('filament-jetstream::emails.team-invitation', ['acceptUrl' => $url])
            ->subject(__('Team Invitation'));
    }
}