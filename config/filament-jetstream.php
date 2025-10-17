<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the user model that will be used by the package.
    |
    */
    'user_model' => env('FILAMENT_JETSTREAM_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Team Model
    |--------------------------------------------------------------------------
    |
    | This is the team model that will be used by the package.
    |
    */
    'team_model' => env('FILAMENT_JETSTREAM_TEAM_MODEL', 'Filament\\Jetstream\\Models\\Team'),

    /*
    |--------------------------------------------------------------------------
    | Role Model
    |--------------------------------------------------------------------------
    |
    | This is the role model that will be used by the package.
    |
    */
    'role_model' => env('FILAMENT_JETSTREAM_ROLE_MODEL', 'Filament\\Jetstream\\Role'),

    /*
    |--------------------------------------------------------------------------
    | Membership Model
    |--------------------------------------------------------------------------
    |
    | This is the membership model that will be used by the package.
    |
    */
    'membership_model' => env('FILAMENT_JETSTREAM_MEMBERSHIP_MODEL', 'Filament\\Jetstream\\Models\\Membership'),

    /*
    |--------------------------------------------------------------------------
    | Team Invitation Model
    |--------------------------------------------------------------------------
    |
    | This is the team invitation model that will be used by the package.
    |
    */
    'team_invitation_model' => env('FILAMENT_JETSTREAM_TEAM_INVITATION_MODEL', 'Filament\\Jetstream\\Models\\TeamInvitation'),

    /*
    |--------------------------------------------------------------------------
    | Features Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which features are enabled by default.
    |
    */
    'features' => [
        'api_tokens' => env('FILAMENT_JETSTREAM_API_TOKENS', true),
        'teams' => env('FILAMENT_JETSTREAM_TEAMS', true),
        'profile_photos' => env('FILAMENT_JETSTREAM_PROFILE_PHOTOS', true),
        'two_factor_authentication' => env('FILAMENT_JETSTREAM_TWO_FACTOR_AUTH', true),
        'passkey_authentication' => env('FILAMENT_JETSTREAM_PASSKEY_AUTH', true),
        'logout_other_sessions' => env('FILAMENT_JETSTREAM_LOGOUT_OTHER_SESSIONS', true),
        'delete_account' => env('FILAMENT_JETSTREAM_DELETE_ACCOUNT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Token Permissions
    |--------------------------------------------------------------------------
    |
    | Define the available API token permissions.
    |
    */
    'api_token_permissions' => [
        'create',
        'read',
        'update',
        'delete',
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Photo Configuration
    |--------------------------------------------------------------------------
    |
    | Configure profile photo settings.
    |
    */
    'profile_photo' => [
        'disk' => env('FILAMENT_JETSTREAM_PROFILE_PHOTO_DISK', 'public'),
        'directory' => env('FILAMENT_JETSTREAM_PROFILE_PHOTO_DIRECTORY', 'profile-photos'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Livewire Components
    |--------------------------------------------------------------------------
    |
    | Override default Livewire components with custom ones.
    |
    */
    'livewire_components' => [
        'api_tokens' => [
            'create' => null, // Set to custom class to override
            'manage' => null, // Set to custom class to override
        ],
        'profile' => [
            'update_information' => null, // Set to custom class to override
            'update_password' => null, // Set to custom class to override
            'logout_other_sessions' => null, // Set to custom class to override
            'delete_account' => null, // Set to custom class to override
        ],
        'teams' => [
            'add_member' => null, // Set to custom class to override
            'delete_team' => null, // Set to custom class to override
            'pending_invitations' => null, // Set to custom class to override
            'team_members' => null, // Set to custom class to override
            'update_team_name' => null, // Set to custom class to override
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Pages
    |--------------------------------------------------------------------------
    |
    | Override default pages with custom ones.
    |
    */
    'pages' => [
        'api_tokens' => null, // Set to custom class to override
        'edit_profile' => null, // Set to custom class to override
        'edit_team' => null, // Set to custom class to override
        'create_team' => null, // Set to custom class to override
        'dashboard' => null, // Set to custom class to override
    ],
];
