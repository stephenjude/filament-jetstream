<?php

namespace Filament\Jetstream\Livewire\Profile;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Jetstream\Agent;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogoutOtherBrowserSessions extends BaseLivewireComponent
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament-jetstream::default.browser_sessions.section.title'))
                    ->description(__('filament-jetstream::default.browser_sessions.section.description'))
                    ->aside()
                    ->schema([
                        Forms\Components\ViewField::make('browserSessions')
                            ->hiddenLabel()
                            ->view('filament-jetstream::components.browser-sessions')
                            ->viewData(['sessions' => self::browserSessions()]),
                        Actions::make([
                            Action::make('deleteBrowserSessions')
                                ->label(__('filament-jetstream::default.action.log_out_other_browsers.label'))
                                ->requiresConfirmation()
                                ->modalHeading(__('filament-jetstream::default.action.log_out_other_browsers.title'))
                                ->modalDescription(
                                    __('filament-jetstream::default.action.log_out_other_browsers.description')
                                )
                                ->modalSubmitActionLabel(
                                    __('filament-jetstream::default.action.log_out_other_browsers.label')
                                )
                                ->modalCancelAction(false)
                                ->schema([
                                    Forms\Components\TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->label(__('filament-jetstream::default.form.password.label'))
                                        ->required()
                                        ->currentPassword(),
                                ])
                                ->action(
                                    fn (array $data) => $this->logoutOtherBrowserSessions($data['password'])
                                ),
                        ]),
                    ]),
            ]);
    }

    public function logoutOtherBrowserSessions(string $password): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        auth(filament()->getAuthGuard())->logoutOtherDevices($password);

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', filament()->auth()->user()->getAuthIdentifier())
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        request()
            ->session()
            ->put([
                'password_hash_' . Auth::getDefaultDriver() => filament()->auth()->user()->getAuthPassword(),
            ]);

        Notification::make()
            ->success()
            ->title(__('filament-jetstream::default.notification.logged_out_other_sessions.success.message'))
            ->send();
    }

    /**
     * Get the current sessions.
     */
    public static function browserSessions(): Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', filament()->auth()->user()->getAuthIdentifier())
            ->orderBy('last_activity', 'desc')
            ->get()->map(function ($session) {
                return (object) [
                    'agent' => tap(new Agent, fn ($agent) => $agent->setUserAgent($session->user_agent)),
                    'ip_address' => $session->ip_address,
                    'is_current_device' => $session->id === request()->session()->getId(),
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });
    }

    public function render(): View
    {
        return view('filament-jetstream::livewire.profile.logout-other-browser-sessions');
    }
}
