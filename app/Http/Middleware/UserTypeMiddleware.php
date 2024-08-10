<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $user_type): Response
    {
        $user = $request->user();
        $allowedRoles = explode(',', $user_type);

        if ($user && in_array($user->user_type, $allowedRoles)) {
            return $next($request);
        }

        return response()->json($allowedRoles);
    }
}
