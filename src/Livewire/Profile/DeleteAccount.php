<?php

namespace Filament\Jetstream\Livewire\Profile;

use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeleteAccount extends BaseLivewireComponent
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament-jetstream::default.delete_account.section.title'))
                    ->description(__('filament-jetstream::default.delete_account.section.description'))
                    ->aside()
                    ->schema([
                        Forms\Components\Placeholder::make('deleteAccountNotice')
                            ->hiddenLabel()
                            ->content(fn () => __('filament-jetstream::default.delete_account.section.notice')),
                        Actions::make([
                            Actions\Action::make('deleteAccount')
                                ->label(__('filament-jetstream::default.action.delete_account.label'))
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading(__('filament-jetstream::default.delete_account.section.title'))
                                ->modalDescription(__('filament-jetstream::default.action.delete_account.notice'))
                                ->modalSubmitActionLabel(__('filament-jetstream::default.action.delete_account.label'))
                                ->modalCancelAction(false)
                                ->form([
                                    Forms\Components\TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->label(__('filament-jetstream::default.form.password.label'))
                                        ->required()
                                        ->currentPassword(),
                                ])
                                ->action(fn (array $data) => $this->deleteAccount()),
                        ]),
                    ])]);
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
                    $this->deletesTeams->delete($team);
                });
            }

            $user->deleteProfilePhoto();

            $user->tokens->each->delete();

            $user->delete();
        });

        app(StatefulGuard::class)->logout();

        return redirect(filament()->getLoginUrl());
    }

    public function render(): View
    {
        return view('filament-jetstream::livewire.profile.delete-account');
    }
}
