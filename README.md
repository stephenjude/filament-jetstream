# Filament Jetstream Enhanced — A Laravel Starter Kit Built With Filament

![Edit Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/banner.jpg)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mominpert/filament-jetstream-enhanced.svg?style=flat-square)](https://packagist.org/packages/mominpert/filament-jetstream-enhanced)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/MominAlZaraa/filament-jetstream/run-tests.yml?branch=1.x&label=tests&style=flat-square)](https://github.com/MominAlZaraa/filament-jetstream/actions?query=workflow%3Arun-tests+branch%3A1.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/MominAlZaraa/filament-jetstream/fix-php-code-styling.yml?branch=1.x&label=code%20style&style=flat-square)](https://github.com/MominAlZaraa/filament-jetstream/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3A1.x)
[![Total Downloads](https://img.shields.io/packagist/dt/mominpert/filament-jetstream-enhanced.svg?style=flat-square)](https://packagist.org/packages/mominpert/filament-jetstream-enhanced)

Filament Jetstream Enhanced is a beautifully designed application starter kit for Laravel, inspired by [Laravel Jetstream](https://jetstream.laravel.com/introduction.html) and built with **Filament**. This enhanced version provides comprehensive customization capabilities and improved developer experience.

Includes auth, registration, 2FA, session management, API tokens, and team support, all implemented with **native Filament panels and components** with **full customization support**.

Skip boilerplate, start building features.

## ✨ Enhanced Features

- 🎨 **Full Customization Support** - Publish and customize any Livewire component
- 🔧 **Dynamic Component Resolution** - Override components through configuration
- 🌍 **Improved Translation System** - Direct lang folder publishing
- ⚙️ **Comprehensive Configuration** - Customize all aspects through config
- 🚀 **Better Developer Experience** - Enhanced publishing and customization workflow
- 🔄 **Dual Naming Convention Support** - Both `::` and `.` Livewire patterns
- 📦 **Modular Publishing** - Publish only what you need

## Installation

You can install the package via composer:

```bash
composer require mominpert/filament-jetstream-enhanced

php artisan filament-jetstream:install --teams --api
```

You can remove the `--teams` and `--api` arguments if you don't want those features.

## 🎨 Customization

### Publishing Components

Publish and customize any component:

```bash
# Publish Livewire components
php artisan vendor:publish --tag=filament-jetstream-livewire

# Publish views
php artisan vendor:publish --tag=filament-jetstream-views

# Publish translations (direct to lang folder)
php artisan vendor:publish --tag=filament-jetstream-translations

# Publish pages
php artisan vendor:publish --tag=filament-jetstream-pages

# Publish all assets
php artisan vendor:publish --tag=filament-jetstream
```

### Configuration

Customize components through the configuration file:

```php
// config/filament-jetstream.php
return [
    'livewire_components' => [
        'profile' => [
            'update_information' => \App\Livewire\Custom\UpdateProfile::class,
            'update_password' => \App\Livewire\Custom\UpdatePassword::class,
        ],
        'api_tokens' => [
            'create' => \App\Livewire\Custom\CreateApiToken::class,
            'manage' => \App\Livewire\Custom\ManageApiTokens::class,
        ],
        'teams' => [
            'add_member' => \App\Livewire\Custom\AddTeamMember::class,
            'team_members' => \App\Livewire\Custom\TeamMembers::class,
        ],
    ],
    'pages' => [
        'edit_profile' => \App\Filament\Pages\Custom\EditProfile::class,
        'api_tokens' => \App\Filament\Pages\Custom\ApiTokens::class,
    ],
];
```

### Translation Customization

Translations are published directly to `lang/en/filament-jetstream.php`:

```php
// lang/en/filament-jetstream.php
return [
    'form' => [
        'name' => [
            'label' => 'Full Name',
        ],
        'email' => [
            'label' => 'Email Address',
        ],
    ],
    // ... customize any translation
];
```

## Features

##### 🔐 Authentication
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/login.jpeg)

##### 👤 User Profile
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/profile.jpeg)

##### 👥 Team (Optional)
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/team.jpeg)

##### 🔑 API Tokens (Optional)
![Profile](https://raw.githubusercontent.com/stephenjude/filament-jetstream/main/art/tokens.jpeg)

##### 🌍 Translation-ready

## Usage & Configurations

#### Configuring the User Profile
```php
use \App\Models\User;
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;

...
JetstreamPlugin::make()
    ->configureUserModel(userModel: User::class)
    ->profilePhoto(condition: fn() => true, disk: 'public')
    ->deleteAccount(condition: fn() => true)
    ->updatePassword(condition: fn() => true, Password::default())
    ->profileInformation(condition: fn() => true)
    ->logoutBrowserSessions(condition: fn() => true)
    ->twoFactorAuthentication(
        condition: fn() => auth()->check(),
        forced: fn() => app()->isProduction(),
        enablePasskey: fn() =>  Feature::active('passkey'),
        requiresPassword: fn() => app()->isProduction(),
    )
```

#### Configuring Team features

```php
use \Filament\Jetstream\Role;
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;
use \Filament\Jetstream\Models\{Team,Membership,TeamInvitation};

...
JetstreamPlugin::make()
    ->teams(
        condition: fn() => Feature::active('teams'), 
        acceptTeamInvitation: fn($invitationId) => JetstreamPlugin::make()->defaultAcceptTeamInvitation()
    )
    ->configureTeamModels(
        teamModel: Team::class,
        roleModel: Role::class,
        membershipModel: Membership::class,
        teamInvitationModel:  TeamInvitation::class
    )
```

#### Configuring API features
```php
use Filament\Jetstream\JetstreamPlugin;
use Illuminate\Validation\Rules\Password;
use \Filament\Jetstream\Role;
use \Filament\Jetstream\Models\{Team, Membership, TeamInvitation};

JetstreamPlugin::make()
    ->apiTokens(
        condition: fn() => Feature::active('api'), 
        permissions: fn() => ['create', 'read', 'update', 'delete'],
        menuItemLabel: fn() => 'API Tokens',
        menuItemIcon: fn() => 'heroicon-o-key',
    ),
```

## Existing Laravel projects

### Installing the Profile feature

#### Publish profile migrations
Run the following command to publish the profile migrations.

```bash
php artisan vendor:publish \
  --tag=filament-jetstream-migrations \
  --tag=passkeys-migrations \
  --tag=filament-two-factor-authentication-migrations
```

#### Add profile feature traits to the User model
Update the `App\Models\User` model:

```php
...
use Filament\Jetstream\HasProfilePhoto;
use Filament\Models\Contracts\HasAvatar;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use \Filament\Jetstream\InteractsWIthProfile;

class User extends Authenticatable implements  HasAvatar, HasPasskeys
{
    ...
    use InteractsWIthProfile;

    protected $hidden = [
        ...
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        ...
        'profile_photo_url',
    ];
}
```

### Installing the Team Features

#### Publish team migration
Run the following command to publish the **team** migrations.
```bash
php artisan vendor:publish --tag=filament-jetstream-team-migrations
```

#### Add team feature traits to User model
Update `App\Models\User` model to implement 'Filament\Models\Contracts\HasTenants' and use `Filament\Jetstream\InteractsWithTeams` trait.

```php
...
use Filament\Jetstream\InteractsWithTeams;
use Filament\Models\Contracts\HasTenants;

class User extends Authenticatable implements  HasTenants
{
    ...
    use InteractsWithTeams;
}

```

### Installing the API Features
#### Publish team migration
Run the following command to publish the **team** migrations.
```bash
php artisan vendor:publish --tag=filament-jetstream-team-migrations
```

#### Add api feature trait to User model
Update `App\Models\User` model to  use `Laravel\Sanctum\HasApiTokens` trait.
```php
...
use \Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable 
{
    use HasApiTokens;
}

```

## 🚀 Advanced Customization

### Custom Livewire Components

Create custom components by extending the base classes:

```php
// app/Livewire/Custom/UpdateProfile.php
use Filament\Jetstream\Livewire\BaseLivewireComponent;

class UpdateProfile extends BaseLivewireComponent
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Full Name')
                    ->required(),
                TextInput::make('surname')
                    ->label('Surname')
                    ->required(),
                // Add your custom fields
            ]);
    }
}
```

### Custom Views

Override any view by publishing and modifying:

```bash
php artisan vendor:publish --tag=filament-jetstream-views
```

Then edit the views in `resources/views/vendor/filament-jetstream/`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- **Original Author**: [stephenjude](https://github.com/stephenjude) - [stephenjude/filament-jetstream](https://github.com/stephenjude/filament-jetstream)
- **Enhanced by**: [mominpert](https://github.com/MominAlZaraa) - [mominpert/filament-jetstream-enhanced](https://github.com/MominAlZaraa/filament-jetstream)
- [taylorotwell](https://github.com/taylorotwell)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, email support@mominpert.com or open an issue on [GitHub](https://github.com/MominAlZaraa/filament-jetstream/issues).