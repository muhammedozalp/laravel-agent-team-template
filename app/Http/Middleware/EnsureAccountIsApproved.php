<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional approval gate (config auth.require_approval, default off): fresh
 * registrations wait at the approval notice until an admin approves them —
 * same middleware pattern as email verification. Admins always pass.
 */
class EnsureAccountIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            config('auth.require_approval')
            && $user !== null
            && ! $user->is_admin
            && ! $user->isApproved()
        ) {
            return redirect()->route('approval.notice');
        }

        return $next($request);
    }
}
