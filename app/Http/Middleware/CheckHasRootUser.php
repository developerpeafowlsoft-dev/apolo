<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHasRootUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
//    public function handle(Request $request, Closure $next): Response
//    {
//        // Check if the application is installed
//        $rootUser = User::role('root')->get();
//        if(!request()->routeIs(['create.root', 'create.superadmin']) && $rootUser->isEmpty()) {
//            return redirect()->route('create.root');
//        }
//        if(!$rootUser->isEmpty() && request()->routeIs(['create.root', 'create.superadmin'])) {
//            return redirect()->route('admin.login')->with('error', 'Super Admin already exists. You cannot create another one.');
//        }
//
//        return $next($request);
//    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if the application is installed
            $rootUser = User::role('root')->get();

            if(!request()->routeIs(['create.root', 'create.superadmin']) && $rootUser->isEmpty()) {
                return redirect()->route('create.root');
            }

            if(!$rootUser->isEmpty() && request()->routeIs(['create.root', 'create.superadmin'])) {
                return redirect()->route('admin.login')->with('error', 'Super Admin already exists.');
            }
        } catch (\Exception $e) {
            // Log the error and attempt to reconnect
            \Log::error('Database connection error: '.$e->getMessage());

            // Try to reconnect
            try {
                DB::reconnect();
                $rootUser = User::role('root')->get();
            } catch (\Exception $e) {
                return response()->view('errors.database', [], 500);
            }
        }

        return $next($request);
    }
}
