<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

$panel = Filament::getCurrentPanel();

Route::redirect('/login', "/{$panel?->getPath()}/login")->name('login');

Route::redirect('/register', "/{$panel?->getPath()}/register")->name('register');

Route::get('/team-invitations/{invitation}', [\Laravel\Jetstream\Http\Controllers\TeamInvitationController::class, 'accept'])
    ->middleware([
        'signed',
        'verified',
        'auth:' . $panel->getAuthGuard(),
        \Illuminate\Session\Middleware\AuthenticateSession::class,
    ])
    ->name('team-invitations.accept');

Route::redirect('/dashboard', "/{$panel?->getPath()}")->name('dashboard');
