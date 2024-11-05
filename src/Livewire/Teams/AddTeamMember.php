<?php

namespace Filament\Jetstream\Livewire\Teams;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Jetstream\Events\InvitingTeamMember;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Mail\TeamInvitation;
use Filament\Jetstream\Rules\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Unique;

class AddTeamMember extends BaseLivewireComponent
{
    public ?array $data = [];

    public function mount(): void
    {
        $data = Filament::getTenant()->only(['name']);

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-jetstream::default.add_team_member.section.title'))
                    ->aside()
                    ->description(__('filament-jetstream::default.add_team_member.section.description'))
                    ->schema([
                        Placeholder::make('addTeamMemberNotice')
                            ->hiddenLabel()
                            ->content(fn () => __('filament-jetstream::default.add_team_member.section.notice')),
                        TextInput::make('email')
                            ->label(__('filament-jetstream::default.form.email.label'))
                            ->email()
                            ->required()
                            ->unique(table: Jetstream::teamInvitationModel(), modifyRuleUsing: function (Unique $rule) {
                                return $rule->where('team_id', Filament::getTenant()->id);
                            })
                            ->validationMessages([
                                'unique' => __('This user has already been invited to the team.'),
                            ])
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if (Filament::getTenant()->hasUserWithEmail($value)) {
                                        $fail(__('This user already belongs to the team.'));
                                    }
                                },
                            ]),
                        Grid::make()
                            ->columns(1)
                            ->schema(function () {
                                $roles = collect(Jetstream::plugin()?->getTeamRolesAndPermissions());

                                return [
                                    Radio::make('role')
                                        ->hiddenLabel()
                                        ->required()
                                        ->in($roles->pluck('key'))
                                        ->options($roles->pluck('name', 'key'))
                                        ->descriptions($roles->pluck('description', 'key')),
                                ];
                            }),
                        Actions::make([
                            Actions\Action::make('addTeamMember')
                                ->label(__('filament-jetstream::default.actions.add_team_member.label'))
                                ->submit('addTeamMember'),
                        ])->alignEnd(),
                    ]),
            ])
            ->statePath('data');
    }

    public function addTeamMember(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        /** @var App/Models/Team $team */
        $team = Filament::getTenant();

        $email = $data['email'];

        $role = $data['role'];

        InvitingTeamMember::dispatch($team, $email, $role);

        $invitation = $team->teamInvitations()->create([
            'email' => $email,
            'role' => $role,
        ]);

        Mail::to($email)->send(new TeamInvitation($invitation));

        $this->sendNotification();

        $this->redirect(Filament::getTenantProfileUrl());
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.add-team-member');
    }
}
