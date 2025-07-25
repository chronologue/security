<?php

namespace Chronologue\Security\Services;

use Chronologue\Core\Support\Service;
use Chronologue\Security\Database\Eloquent\User;
use Chronologue\Security\Support\KeycloakProvider;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthService extends Service
{
    public function provider(): KeycloakProvider
    {
        return Socialite::driver('keycloak');
    }

    /**
     * @throws AuthenticationException
     */
    public function login(): void
    {
        /** @var KeycloakProvider $provider */
        $provider = Socialite::driver('keycloak');

        if (is_null($user = $provider->user())) {
            throw new AuthenticationException();
        }

        $model = $this->updateOrCreateModel($user);

        Auth::login($model);
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