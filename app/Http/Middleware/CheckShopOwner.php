<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckShopOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && ($user->shop_id === null || $user->hasRole('admin') || $user->hasRole('root'))) {
            return $next($request);
        }

        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $allowedShopIds = array_filter(array_unique([$shop?->id, $rootShop?->id, 1, 14]));

        $parameters = $request->route()->parameters();

        foreach ($parameters as $param) {
            if (is_object($param) && isset($param->shop_id)) {
                if ($param->shop_id !== null && !in_array($param->shop_id, $allowedShopIds)) {
                    $routeName = $request->route()->getName();
                    $prefix = substr($routeName, 0, strrpos($routeName, '.'));

                    return to_route($prefix . '.index')
                        ->withError('You are not authorized to access this resource.');
                }
            }
        }

        return $next($request);
    }
}
