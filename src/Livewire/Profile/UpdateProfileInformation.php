<?php

namespace Filament\Jetstream\Livewire\Profile;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Arr;

class UpdateProfileInformation extends BaseLivewireComponent
{
    public ?array $data = [];

    public function mount(): void
    {
        $data = $this->authUser()->only(['name', 'email']);

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-jetstream::default.update_profile_information.section.title'))
                    ->aside()
                    ->description(__('filament-jetstream::default.update_profile_information.section.description'))
                    ->schema([
                        FileUpload::make('profile_photo_path')
                            ->label(__('filament-jetstream::default.form.profile_photo.label'))
                            ->avatar()
                            ->imageEditor()
                            ->directory('profile-photos')
                            ->disk(fn (): string => Jetstream::plugin()?->profilePhotoDisk())
                            ->visible(fn (): bool => Jetstream::plugin()?->managesProfilePhotos()),
                        TextInput::make('name')
                            ->label(__('filament-jetstream::default.form.name.label'))
                            ->string()
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('email')
                            ->label(__('filament-jetstream::default.form.email.label'))
                            ->email()
                            ->required()
                            ->unique(get_class(Filament::auth()->user()), ignorable: $this->authUser()),
                        Actions::make([
                            Actions\Action::make('save')
                                ->label(__('filament-jetstream::default.action.save.label'))
                                ->submit('updateProfile'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function updateProfile(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        $user = $this->authUser();

        $isUpdatingEmail = $data['email'] !== $user->email;

        $isUpdatingPhoto = $data['profile_photo_path'] !== $user->profile_photo_path;

        $user->fill(Arr::except($data, ['profile_photo_path']));

        $user->save();

        if ($isUpdatingEmail) {
            $user->forceFill(['email_verified_at' => null]);

            $user->sendEmailVerificationNotification();
        }

        if ($isUpdatingPhoto) {
            Arr::get($data, 'profile_photo_path')
                ? $user->updateProfilePhoto($data['profile_photo_path'])
                : $user->deleteProfilePhoto();
        }

        $this->sendNotification();
    }

    public function render()
    {
        return view('filament-jetstream::livewire.profile.update-profile-information');
    }
}
