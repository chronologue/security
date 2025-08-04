<?php

namespace Chronologue\Security\Events;

use Chronologue\Security\Database\Eloquent\User;
use Chronologue\Security\Support\KeycloakProvider;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class AuthEventSubscriber
{
    public function handleLogin(): void
    {
        Session::regenerate();
    }

    public function handleLogout(Logout $event): void
    {
        /** @var User $user */
        $user = $event->user;

        /** @var KeycloakProvider $provider */
        $provider = Socialite::driver('keycloak');
        $provider->logout($user->getAttribute('access_token'), $user->getAttribute('refresh_token'));

        Session::invalidate();
        Session::regenerate();
    }

    public function handleAuthenticated(Authenticated $event): void
    {
        Inertia::share('auth', fn() => $event->user);
    }

    public function subscribe(): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Authenticated::class => 'handleAuthenticated',
        ];
    }
}