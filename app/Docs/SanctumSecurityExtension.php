<?php

namespace App\Docs;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\RouteInfo;

/**
 * Adds Bearer security requirements only to routes protected by the
 * auth:sanctum middleware. Public routes remain unlocked in the docs.
 */
class SanctumSecurityExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $middleware = $routeInfo->route->gatherMiddleware();

        $isProtected = collect($middleware)->contains(
            fn ($m) => str_contains($m, 'auth:sanctum') || str_contains($m, 'auth.sanctum')
        );

        if ($isProtected) {
            $operation->addSecurity(new SecurityRequirement(['bearerAuth' => []]));
        }
    }
}
