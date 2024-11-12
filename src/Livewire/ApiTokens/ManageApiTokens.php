<?php

namespace Filament\Jetstream\Livewire\ApiTokens;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Laravel\Sanctum\PersonalAccessToken;

class ManageApiTokens extends BaseLivewireComponent implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->authUser()->tokens()->latest())
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('name'),
                ]),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('updateToken')
                    ->label(__('filament-jetstream::default.actions.update_token.label'))
                    ->modalHeading(__('filament-jetstream::default.actions.update_token.title'))
                    ->modalWidth('lg')
                    ->modalCancelAction(false)
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->modalSubmitActionLabel(__('filament-jetstream::default.actions.update_token.modal.label'))
                    ->form(fn (PersonalAccessToken $record, Form $form) => $form->schema(fn () => collect(Jetstream::plugin()->getApiTokenPermissions())
                        ->map(fn ($permission) => Checkbox::make($permission)->label(__($permission))->default($record->can($permission)))
                        ->toArray())
                        ->columns())
                    ->action(fn ($record, array $data) => $this->updateToken($record, $data)),
                Tables\Actions\Action::make('deleteToken')
                    ->color('danger')
                    ->label(__('filament-jetstream::default.actions.delete_token.label'))
                    ->modalHeading(__('filament-jetstream::default.actions.delete_token.title'))
                    ->modalDescription(__('filament-jetstream::default.actions.delete_token.description'))
                    ->action(fn ($record) => $this->deleteToken($record)),
            ]);
    }

    public function updateToken(PersonalAccessToken $record, array $data)
    {
        $record->forceFill([
            'abilities' => Jetstream::plugin()->validPermissions(array_keys(array_filter($data))),
        ])->save();

        $this->sendNotification();
    }

    public function deleteToken(PersonalAccessToken $record)
    {
        $record->delete();

        $this->sendNotification(__('Token deleted!'));
    }

    public function render()
    {
        return view('filament-jetstream::livewire.api-tokens.manage-api-tokens');
    }
}
