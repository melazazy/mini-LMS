<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructorOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canManageContent()) {
            abort(403, 'Unauthorized access. Instructor or Admin role required.');
        }

        return $next($request);
    }
}