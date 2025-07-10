<?php

namespace Chronologue\Security\Services;

use Chronologue\Core\Exceptions\UnauthenticatedException;
use Chronologue\Core\Support\Service;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Chronologue\Security\Database\Eloquent\User;
use Chronologue\Security\Support\KeycloakProvider;

class AuthService extends Service
{
    public function provider(): KeycloakProvider
    {
        return Socialite::driver('keycloak');
    }

    public function login(): void
    {
        /** @var KeycloakProvider $provider */
        $provider = Socialite::driver('keycloak');

        if (is_null($user = $provider->user())) {
            throw new UnauthenticatedException(__('Invalid authenticated user.'));
        }

        $model = User::query()->updateOrCreate(['sub' => $user->getId()], [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'permissions' => $user['permissions'],
            'access_token' => $user->token,
            'refresh_token' => $user->refreshToken,
        ]);

        Auth::login($model);
    }

    public function logout(): void
    {
        Auth::logout();
    }
}