<?php

use App\Exceptions\StudyLockedException;
use App\Http\Middleware\AccessStudy;
use App\Http\Middleware\ActiveUser;
use App\Http\Middleware\Admin;
use App\Http\Middleware\EnsureApiEnabled;
use App\Http\Middleware\EnsureStudyEditable;
use App\Http\Middleware\ModifyStudy;
use App\Http\Middleware\OptionalSanctumAuth;
use App\Http\Middleware\RestrictEmbargo;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustHosts(at: fn () => config('app.trusted_hosts'));

        $middleware->api(prepend: [
            EnsureApiEnabled::class,
            HandleCors::class,
            OptionalSanctumAuth::class,
            ActiveUser::class,
        ]);

        $middleware->alias([
            'admin' => Admin::class,
            'access-study' => AccessStudy::class,
            'modify-study' => ModifyStudy::class,
            'restrict-embargo' => RestrictEmbargo::class,
            'ensure-study-editable' => EnsureStudyEditable::class,
            'active-user' => ActiveUser::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // provide proper error page when trying to modify a locked study
        $exceptions->render(function (StudyLockedException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 403);
            }

            abort(403, $exception->getMessage());
        });
    })->create();
