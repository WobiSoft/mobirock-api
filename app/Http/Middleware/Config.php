<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Config
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $uuid = $request->header('uuid');
        $platform = $request->header('platform');

        if (!$uuid || !$platform)
        {
            return response()->json([
                'message' => 'Ocurrió un error con tu solicitud.',
                'error' => '98111'
            ], 400);
        }

        $request->config = (object) [
            'uuid' => $uuid,
            'platform' => $platform
        ];

        return $next($request);
    }
}
