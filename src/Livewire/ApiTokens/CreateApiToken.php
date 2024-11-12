<?php

namespace Filament\Jetstream\Livewire\ApiTokens;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Attributes\On;

class CreateApiToken extends BaseLivewireComponent
{
    public ?array $data = [];

    public function mount(): void {}

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-jetstream::default.create_api_token.section.title'))
                    ->aside()
                    ->description(__('filament-jetstream::default.create_api_token.section.description'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament-jetstream::default.form.token_name.label'))
                            ->minLength(3)
                            ->required()
                            ->string()
                            ->maxLength(255),
                        Placeholder::make('permissions')
                            ->label(__('filament-jetstream::default.form.permissions.label')),
                        Grid::make()
                            ->columns()
                            ->schema(
                                fn () => collect(Jetstream::plugin()->getApiTokenPermissions())
                                    ->map(fn ($permission) => Checkbox::make($permission)->label(__($permission)))
                                    ->toArray()
                            ),

                        Actions::make([
                            Actions\Action::make('save')
                                ->label(__('filament-jetstream::default.actions.create_token.label'))
                                ->submit('createToken'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function createToken(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        $permissions = Jetstream::plugin()->validPermissions(array_keys(array_filter(Arr::except($data, 'name'))));

        if (empty($permissions)) {
            Notification::make()
                ->danger()
                ->body(__('filament-jetstream::default.notifications.create_token.error.message'))
                ->send();

            return;
        }

        /** @var PersonalAccessToken $token */
        $token = $this->authUser()->createToken($data['name'], $permissions);

        $plainTextToken = explode('|', $token->plainTextToken, 2)[1];

        $this->data = [];

        Notification::make('showApiToken')
            ->success()
            ->body(new HtmlString("API Token: <span>{$plainTextToken}</span>"))
            ->title(__('filament-jetstream::default.notifications.create_token.success.message'))
            ->actions([
                Action::make('copy')
                    ->label(__('filament-jetstream::default.actions.copy_token.label'))
                    ->icon('heroicon-o-square-2-stack')
                    ->alpineClickHandler('(window.navigator.clipboard.writeText("' . $plainTextToken . '"))')
                    ->dispatch('token-copied', ['token' => $plainTextToken]),
            ])
            ->persistent()
            ->send();

        $this->redirect(Jetstream::plugin()->getApiTokenUrl(Filament::getCurrentPanel()));
    }

    public function render()
    {
        return view('filament-jetstream::livewire.api-tokens.create-api-token');
    }

    #[On('token-copied')]
    public function sendTokenCopiedNotification(): void
    {
        Notification::make()
            ->success()
            ->body(__('filament-jetstream::default.notifications.copy_token.success.message'))
            ->icon('heroicon-o-square-2-stack')
            ->send();

        $this->dispatch('close-notification', id: 'showApiToken');
    }
}
