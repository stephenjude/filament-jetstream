<?php

return [
    'form' => [

        'name' => [
            'label' => 'Name',
        ],

        'team_owner' => [
            'label' => 'Team Owner',
        ],

        'email' => [
            'label' => 'Email',
        ],

        'password' => [
            'label' => 'Password',
        ],

        'profile_photo' => [
            'label' => 'Photo',
        ],

        'current_password' => [
            'label' => 'Current Password',
        ],

        'new_password' => [
            'label' => 'New Password',
        ],

        'new_password_confirmation' => [
            'label' => 'Confirm Password',
        ],

        'token_name' => [
            'label' => 'Token Name',
        ],

        'permissions' => [
            'label' => 'Permissions',
        ],

        'team_name' => [
            'label' => 'Team Name',
        ],
    ],

    'table' => [

        'columns' => [

            'token_name' => [
                'label' => 'Tokens',
            ],

            'pending_invitations' => [
                'label' => 'Pending Invitations',
            ],

            'team_members' => [
                'label' => 'Members',
            ],

            'role' => [
                'label' => 'Role',
            ],

        ],

    ],

    'notifications' => [

        'save' => [

            'success' => [
                'message' => 'Saved.',
            ],

        ],

        'create_token' => [

            'success' => [
                'message' => 'Please copy your new API token. For your security, it won\'t be shown again.',
            ],

            'error' => [
                'message' => 'Select at least one permission.',
            ],

        ],

        'copy_token' => [

            'success' => [
                'message' => 'copied to clipboard',
            ],

        ],

    ],

    'actions' => [

        'save' => [
            'label' => 'Save',
        ],

        'update_token' => [

            'title' => 'API Token Permissions',

            'label' => 'Permissions',

            'modal' => [
                'label' => 'Save',
            ],

        ],

        'delete_token' => [

            'title' => 'Delete API Token',

            'description' => 'Are you sure you would like to delete this API token?',

            'label' => 'Remove',

        ],

        'delete_account' => [

            'label' => 'Delete Account',

            'notice' => 'Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',

        ],

        'delete_team' => [

            'label' => 'Delete Team',

            'notice' => 'Are you sure you want to delete this team? Once a team is deleted, all of its resources and data will be permanently deleted.',

        ],

        'create_token' => [
            'label' => 'Create Token',
        ],

        'copy_token' => [
            'label' => 'Copy',
        ],

        'add_team_member' => [
            'label' => 'Add',
        ],

        'update_team_role' => [
            'title' => 'Manage Role',
        ],

        'remove_team_member' => [

            'label' => 'Remove',

            'notice' => 'Are you sure you would like to remove this team member?',
        ],

        'leave_team' => [

            'label' => 'Leave',

            'notice' => 'Are you sure you would like to leave this team?',
        ],

        'cancel_team_invitation' => [
            'label' => 'Cancel',
        ],
    ],

    'update_profile_information' => [

        'section' => [

            'title' => 'Profile Information',

            'description' => 'Update your account\'s profile information and email address.',

        ],

    ],

    'update_password' => [

        'section' => [

            'title' => 'Update Password',

            'description' => 'Ensure your account is using a long, random password to stay secure.',

        ],

    ],

    'delete_account' => [

        'section' => [

            'title' => 'Delete Account',

            'description' => 'Permanently delete your account.',

            'notice' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',

        ],

    ],

    'create_api_token' => [

        'section' => [

            'title' => 'Create API Token',

            'description' => 'API tokens allow third-party services to authenticate with our application on your behalf.',

        ],

    ],

    'manage_api_tokens' => [

        'section' => [

            'title' => 'Manage API Tokens',

            'description' => 'You may delete any of your existing tokens if they are no longer needed.',

        ],

    ],

    'browser_sessions' => [

        'section' => [

            'title' => 'Browser Sessions',

            'description' => 'Manage and log out your active sessions on other browsers and devices.',

            'notice' => 'If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.',

            'labels' => [

                'current_device' => 'This device',

                'last_active' => 'Last active',

            ],

        ],

        'actions' => [

            'log_out_other_browsers' => [

                'label' => 'Log Out Other Browser Sessions',

                'title' => 'Log Out Other Browser Sessions',

                'description' => 'Enter your password to confirm you would like to log out of your other browser sessions across all of your devices.',

            ],

        ],

        'notifications' => [

            'success' => [
                'message' => 'All other browser sessions have been logged out successfully.',
            ],

        ],

    ],

    'update_team_name' => [

        'section' => [

            'title' => 'Team Name',

            'description' => 'The team\'s name and owner information.',

        ],

    ],

    'add_team_member' => [

        'section' => [

            'title' => 'Add Team Member',

            'description' => 'Add a new team member to your team, allowing them to collaborate with you.',

            'notice' => 'Please provide the email address of the person you would like to add to this team.',

        ],

    ],

    'team_members' => [

        'section' => [

            'title' => 'Team Members',

            'description' => 'All of the people that are part of this team.',

        ],

    ],

    'pending_team_invitations' => [

        'section' => [

            'title' => 'Pending Team Invitations',

            'description' => 'These people have been invited to your team and have been sent an invitation email. They may join the team by accepting the email invitation.',

        ],

    ],

    'delete_team' => [

        'section' => [

            'title' => 'Delete Team',

            'description' => 'Permanently delete this team.',

            'notice' => 'Once a team is deleted, all of its resources and data will be permanently deleted. Before deleting this team, please download any data or information that you wish to retain.',

        ],

    ],

];
