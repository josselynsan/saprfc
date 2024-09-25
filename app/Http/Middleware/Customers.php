<?php

namespace App\Http\Middleware;

//use App\Models\Customer;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;

class Customers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (!$request->header('x-api-key')) {
                return response()->json([
                    'No autorizado',
                ],401);
            } else {
                if (env('SAP_RFC_KEY') == $request->header('x-api-key')) {
                    return $next($request);
                } else {
                    return response()->json([
                        'No autorizado',
                    ],401);

                }
            }
            return response()->json([
                'No autorizado',
            ],401);

        } catch (\Exception $e) {
            return response()->json([
                'No autorizado',
            ],401);
        }
    }
}
