<?php

namespace Chronologue\Security\Services;

use Chronologue\Core\Support\Service;
use Chronologue\Security\Database\Eloquent\User;
use Chronologue\Security\Support\KeycloakProvider;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;

class AuthService extends Service
{
    public function provider(): KeycloakProvider
    {
        $driver = Socialite::driver('keycloak');

        if (!($driver instanceof KeycloakProvider)) {
            throw new RuntimeException('Invalid KeycloakProvider.');
        }

        return $driver;
    }

    /**
     * @throws AuthenticationException
     */
    public function login(): void
    {
        $provider = $this->provider();

        if (is_null($user = $provider->user())) {
            throw new AuthenticationException();
        }

        Auth::login($this->updateOrCreateModel($user));
    }

    public function logout(): void
    {
        Auth::logout();
    }

    protected function updateOrCreateModel($user): User
    {
        $sub = $user->getId();

        return User::query()->updateOrCreate(compact('sub'), [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'permissions' => $user['permissions'],
            'access_token' => $user->token,
            'refresh_token' => $user->refreshToken,
        ]);
    }
}