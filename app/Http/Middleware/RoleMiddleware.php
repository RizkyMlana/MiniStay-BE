<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     */

    protected string $role;

    public function __construct(string $role = '')
    {
        $this->role = $role;
    }

    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();
        $requiredRole = $role ?? $this->role;

        if(!$user || $user->role !== $requiredRole) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        return $next($request);
    }
}
