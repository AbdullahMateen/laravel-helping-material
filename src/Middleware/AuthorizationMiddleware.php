<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Middleware;

use AbdullahMateen\LaravelHelpingMaterial\Enums\User\RoleEnum;
use Closure;
use Illuminate\Http\Request;

class AuthorizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed   ...$userLevel
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$userLevels)
    {
        $levels = [];
        foreach ($userLevels as $userLevel) {
            $levels[] = $userLevel instanceof RoleEnum ? $userLevel->value : $userLevel;
        }

        abort_unless(is_level($levels), 404);
        return $next($request);
    }
}
