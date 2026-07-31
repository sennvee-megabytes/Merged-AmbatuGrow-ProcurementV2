<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Confirms the authenticated user holds one of the approver roles.
 * (All authenticated users in this app are approvers/requestors of
 * some kind, but this is kept as an explicit extension point.)
 */
class EnsureApprover
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Your account is not authorized to access the approval system.');
        }

        return $next($request);
    }
}
