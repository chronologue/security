<?php

namespace Chronologue\Security;

use Chronologue\Core\Support\ModuleServiceProvider;
use Chronologue\Security\Events\Subscribers\AuthEventSubscriber;
use Chronologue\Security\Support\KeycloakProvider;
use Illuminate\Support\Facades\Event;
use ReflectionClass;
use SocialiteProviders\Manager\SocialiteWasCalled;

class ServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        $this->bootSocialiteEvent();
        $this->bootAuthEventSubscriber();
    }

    protected function bootSocialiteEvent(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('keycloak', KeycloakProvider::class);
        });
    }

    protected function bootAuthEventSubscriber(): void
    {
        Event::subscribe(AuthEventSubscriber::class);
    }

    protected function initDirectory(): void
    {
        $class = new ReflectionClass(self::class);
        $this->directory = dirname($class->getFileName());
    }

}
