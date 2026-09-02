<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Branch;

class AuthenticateBranchApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Branch-API-Key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'API Key is missing.'
            ], 401);
        }

        $branch = Branch::where('api_key', $apiKey)->where('is_active', true)->first();

        if (!$branch) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or inactive Branch API Key.'
            ], 401);
        }

        $request->attributes->set('active_branch', $branch);

        return $next($request);
    }
}
