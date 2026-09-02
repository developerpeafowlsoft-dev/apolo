<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\BranchContext;

class EnforceBranchModeRestrictions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $branchContext = app(BranchContext::class);

        if ($branchContext->isBranch()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'This action is disabled in offline branch mode.'
                ], 503);
            }

            abort(503, 'This action is disabled in offline branch mode.');
        }

        return $next($request);
    }
}
