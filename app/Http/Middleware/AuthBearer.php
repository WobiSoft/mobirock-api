<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthBearer
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
        $token = $request->bearerToken();

        if (!$token)
        {
            return response()->json([
                'message' => 'Este recurso requiere autorización.',
                'error' => '00001'
            ], 401);
        }

        $uuid = $request->header('uuid');

        if (!$uuid)
        {
            return response()->json([
                'message' => 'Este recurso requiere autorización.',
                'error' => '00002'
            ], 401);
        }

        $session = UserSession::whereSesionToken($token)
            ->whereSesionDispositivo($uuid)
            ->whereSesionStatus(1)
            ->orderBy('sesion_id', 'DESC')
            ->first();

        if (!$session)
        {
            return response()->json([
                'message' => 'Este recurso requiere autorización.',
                'error' => '00003'
            ], 401);
        }

        if (!is_null($session->ends_at) && ($session->ends_at < date('Y-m-d H:i:s')))
        {
            $session->update([
                'sesion_logout' => date('Y-m-d H:i:s'),
                'sesion_status' => 0
            ]);

            return response()->json([
                'message' => 'Este recurso requiere autorización.',
                'error' => '00004'
            ], 401);
        }

        try
        {
            $decoded = JWT::decode($token, new Key(config('app.key'), 'HS256'));

            $user = User::find($decoded->id);

            if (!$user)
            {
                $session->update([
                    'sesion_logout' => date('Y-m-d H:i:s'),
                    'sesion_status' => 0
                ]);

                return response()->json([
                    'message' => 'Has perdido acceso a tu cuenta. Comunícate con tu distribuidor para aclarar esta situación.',
                    'error' => '00006'
                ], 401);
            }

            if ($user->status->id === 0)
            {
                $session->update([
                    'sesion_logout' => date('Y-m-d H:i:s'),
                    'sesion_status' => 0
                ]);

                return response()->json([
                    'message' => 'Tu cuenta ha sido eliminada. Comunícate con tu distribuidor para aclarar esta situación.',
                    'error' => '00007'
                ], 401);
            }

            if ($user->status->id === 2)
            {
                $session->update([
                    'sesion_logout' => date('Y-m-d H:i:s'),
                    'sesion_status' => 0
                ]);

                return response()->json([
                    'message' => 'Debes activar tu cuenta para acceder a este recurso.',
                    'error' => '00008'
                ], 401);
            }

            if ($user->config->blocked)
            {
                $session->update([
                    'sesion_logout' => date('Y-m-d H:i:s'),
                    'sesion_status' => 0
                ]);

                return response()->json([
                    'message' => 'Por el momento tu acceso esta bloqueado. Comunícate con tu distribuidor para aclarar esta situacion.',
                    'error' => '00009'
                ], 401);
            }

            if ($session->user_id !== $user->id)
            {
                $session->update([
                    'sesion_logout' => date('Y-m-d H:i:s'),
                    'sesion_status' => 0
                ]);

                return response()->json([
                    'message' => 'Este recurso requiere autorización.',
                    'error' => '00010'
                ], 401);
            }

            $request->auth = (object) [
                'user' => $user,
                'parent' => (in_array($user->type_id, [2, 4, 6, 8, 10]) ? $user->parent : $user),
                'session' => $session
            ];

            return $next($request);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Este recurso requiere autorización.',
                'error' => '00005'
            ], 401);
        }
    }
}
