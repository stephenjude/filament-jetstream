<?php

use Filament\Facades\Filament;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController;

$panel = Filament::getCurrentPanel();

Route::redirect('/login', "/{$panel?->getPath()}/login")->name('login');

Route::redirect('/register', "/{$panel?->getPath()}/register")->name('register');

$authGuard = $panel ? 'auth:'.$panel?->getAuthGuard() : 'auth';

Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
    ->middleware(['signed', 'verified', $authGuard, AuthenticateSession::class])
    ->name('team-invitations.accept');

Route::redirect('/dashboard', "/{$panel?->getPath()}")->name('dashboard');
