<?php

namespace Chronologue\Security\Support;

use Chronologue\Core\Exceptions\AccessDeniedException;
use Chronologue\Security\Support\Attributes\Permission;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionMethod;

class EnsurePermission
{
    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Closure $next)
    {
        if (empty($permission = $this->getRequiredPermission($request))) {
            return $next($request);
        }

        if ($request->user()?->permitted($permission)) {
            return $next($request);
        }

        throw new AccessDeniedException();
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    private function getRequiredPermission(Request $request): array
    {
        if (empty($controller = $request->route()->getController())) {
            return [];
        }

        $method = Str::parseCallback($request->route()->getAction('uses'))[1];
        $reflection = new ReflectionMethod($controller, $method);

        return collect($reflection->getAttributes(Permission::class))
            ->map(fn($attribute) => $attribute->getArguments())
            ->flatten()
            ->toArray();
    }
}
