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
        $shop = generaleSetting('shop');
        $parameters = $request->route()->parameters();

        foreach ($parameters as $param) {

            if (is_object($param) && isset($param->shop_id)) {
                if ($param->shop_id !== $shop->id) {
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
