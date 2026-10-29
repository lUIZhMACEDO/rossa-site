<?php

namespace App\Http\Middleware;

use App\Enums\PermissionType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictEmbargo
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $study = $request->route('study');
        $user = $request->user();

        $canManageStudies = $user && $user->hasPermission(PermissionType::MANAGE_STUDIES->value);
        $canEditStudy = $user && $study && $user->canEditStudy($study);

        if ($study && $study->isEmbargoed() && ! $canManageStudies && ! $canEditStudy) {
            return redirect()->back()->with('error', 'This study is currently under embargo and cannot be accessed.');
        }

        return $next($request);
    }
}
