<?php

namespace Chronologue\Security\Support;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Keycloak\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use Throwable;

class KeycloakProvider extends Provider
{
    public function user(): ?User
    {
        return tap(parent::user(), function (User $user) {
            $user['permissions'] = $this->requestPermissions($user->token);
        });
    }

    public function requestPermissions(string $token): array
    {
        try {
            $response = $this->getHttpClient()->post($this->getBaseUrl() . '/protocol/openid-connect/token', [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                RequestOptions::FORM_PARAMS => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:uma-ticket',
                    'audience' => $this->getConfig('client_id'),
                    'response_mode' => 'permissions',
                ],
            ]);

            return $this->parsePermissions(
                json_decode((string)$response->getBody(), true)
            );
        } catch (Throwable) {
            return [];
        }
    }

    public function logout(string $token, string $refreshToken): void
    {
        try {
            $this->getHttpClient()->post($this->getLogoutUrl(), [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                RequestOptions::FORM_PARAMS => [
                    'client_id' => $this->getConfig('client_id'),
                    'client_secret' => $this->getConfig('client_secret'),
                    'refresh_token' => $refreshToken,
                ],
            ]);
        } catch (Throwable) {
            //
        }
    }

    protected function parsePermissions(array $rawData): array
    {
        $permissions = collect();
        foreach ($rawData as $permission) {
            foreach ($permission['scopes'] as $scope) {
                $permissions->add($scope . ':' . $permission['rsname']);
            }
        }
        return $permissions->unique()->all();
    }
}