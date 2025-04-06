<?php

return [
    /*
     * You can customize some of the behavior of this package by using our own custom model.
     * Your custom model should always extend the one of the default ones .
     */
    'models' => [
        'user' => \App\Models\User::class,
        'team' => \Filament\Jetstream\Models\Team::class,
        'membership' => \Filament\Jetstream\Models\Membership::class,
        'invitation' => \Filament\Jetstream\Models\TeamInvitation::class,
    ],
];
