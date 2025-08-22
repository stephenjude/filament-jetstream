<?php

namespace Filament\Jetstream\Livewire\Teams;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Jetstream\Events\InvitingTeamMember;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Mail\TeamInvitation;
use Filament\Jetstream\Models\Team;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Unique;

class AddTeamMember extends BaseLivewireComponent
{
    public ?array $data = [];

    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;

        $this->form->fill($this->team->only(['name']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Section::make(__('filament-jetstream::default.add_team_member.section.title'))
                    ->aside()
                    ->visible(fn () => Gate::check('addTeamMember', $this->team))
                    ->description(__('filament-jetstream::default.add_team_member.section.description'))
                    ->schema([
                        Placeholder::make('addTeamMemberNotice')
                            ->hiddenLabel()
                            ->content(fn () => __('filament-jetstream::default.add_team_member.section.notice')),
                        TextInput::make('email')
                            ->label(__('filament-jetstream::default.form.email.label'))
                            ->email()
                            ->required()
                            ->unique(table: Jetstream::plugin()->teamInvitationModel(), modifyRuleUsing: function (
                                Unique $rule
                            ) {
                                return $rule->where(
                                    Jetstream::getForeignKeyColumn(Jetstream::plugin()->teamModel()),
                                    $this->team->id
                                );
                            })
                            ->validationMessages([
                                'unique' => __(
                                    'filament-jetstream::default.action.add_team_member.error_message.email_already_invited'
                                ),
                            ])
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if ($this->team->hasUserWithEmail($value)) {
                                        $fail(
                                            __(
                                                'filament-jetstream::default.action.add_team_member.error_message.email_already_invited'
                                            )
                                        );
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
                            Action::make('addTeamMember')
                                ->label(__('filament-jetstream::default.action.add_team_member.label'))
                                ->action(function () {
                                    $this->addTeamMember($this->team);
                                }),
                        ])->alignEnd(),
                    ]),
            ]);
    }

    public function addTeamMember(Team $team): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        $email = $data['email'];

        $role = $data['role'];

        InvitingTeamMember::dispatch($team, $email, $role);

        $invitation = $team->teamInvitations()->create([
            'email' => $email,
            'role' => $role,
        ]);

        Mail::to($email)->send(new TeamInvitation($invitation));

        $this->sendNotification(__('filament-jetstream::default.notification.team_invitation_sent.success.message'));

        $this->redirect(Filament::getTenantProfileUrl());
    }

    public function render()
    {
        return view('filament-jetstream::livewire.teams.add-team-member');
    }
}
