<?php

namespace Chronologue\Security\Http\Controllers;

use Chronologue\Core\Support\Controller;
use Laravel\Socialite\Two\InvalidStateException;
use Chronologue\Security\Services\AuthService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function redirect(): Response
    {
        return $this->builder()->factory()->location(
            $this->service->provider()->redirect()
        );
    }

    public function callback(): RedirectResponse
    {
        try {
            $this->service->login();
            return $this->redirector()->intended();
        } catch (InvalidStateException) {
            return $this->redirector()->route('login');
        }
    }

    public function logout(): Response
    {
        $this->service->logout();
        return $this->redirector()->route('login');
    }
}