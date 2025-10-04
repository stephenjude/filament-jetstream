<?php

namespace Filament\Jetstream\Livewire\Profile;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeleteAccount extends BaseLivewireComponent
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament-jetstream::default.delete_account.section.title'))
                    ->description(__('filament-jetstream::default.delete_account.section.description'))
                    ->aside()
                    ->schema([
                        TextEntry::make('deleteAccountNotice')
                            ->hiddenLabel()
                            ->state(fn() => __('filament-jetstream::default.delete_account.section.notice')),
                        Actions::make([
                            Action::make('deleteAccount')
                                ->label(__('filament-jetstream::default.action.delete_account.label'))
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading(__('filament-jetstream::default.delete_account.section.title'))
                                ->modalDescription(__('filament-jetstream::default.action.delete_account.notice'))
                                ->modalSubmitActionLabel(__('filament-jetstream::default.action.delete_account.label'))
                                ->modalCancelAction(false)
                                ->schema([
                                    Forms\Components\TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->label(__('filament-jetstream::default.form.password.label'))
                                        ->required()
                                        ->currentPassword(),
                                ])
                                ->action(fn(array $data) => $this->deleteAccount())
                                ->successNotificationTitle('User deleted')
                                ->successRedirectUrl('/HealthcareProvider'),
                        ]),
                    ])
            ]);
    }

    /**
     * Delete the current user.
     */
    public function deleteAccount(): Redirector | RedirectResponse
    {
        $user = filament()->auth()->user();

        DB::transaction(function () use ($user) {
            if (Jetstream::plugin()?->hasTeamsFeatures()) {
                $user->teams()->detach();

                $user->ownedTeams->each(function (Team $team) {
                    $team->delete();
                });
            }

            $user->deleteProfilePhoto();

            $user->tokens?->each->delete();

            $user->delete();
        });

        Auth::logout();

        return redirect(filament()->getLoginUrl());
    }

    public function render(): View
    {
        return view('filament-jetstream::livewire.profile.delete-account');
    }
}
