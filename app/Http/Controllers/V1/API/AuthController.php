<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AuthCode;
use App\Http\Requests\V1\AuthValidate;
use App\Http\Requests\V1\AuthForgotten;
use App\Http\Requests\V1\AuthLogin;
use App\Http\Requests\V1\AuthRenew;
use App\Http\Requests\V1\ChangePassword;
use App\Models\Connection;
use App\Models\Device;
use App\Models\UserSession;
use App\Models\User;
use App\Notifications\ForgottenPassword;
use App\Notifications\RenewPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;

use Jenssegers\Agent\Agent;

class AuthController extends Controller
{

    public function login(AuthLogin $request)
    {
        $data = $request->validated();

        $user = User::whereConcesionarioNumero($data['username'])
            ->where('concesionario_status', '!=', 0)
            ->with(['type'])
            ->first();

        $able = $user->able();

        if (!$able->status)
        {
            return response()->json(['message' => $able->message], $able->code);
        }

        $user->append('password');

        if (!Hash::check($data['password'], $user->password))
        {
            return response()->json([
                'message' => 'Esta Contraseña es incorrecta.'
            ], 401);
        }

        $agent = new Agent();

        Device::updateOrCreate([
            'dispositivo_concesionario' => $user->id,
            'dispositivo_uuid' => request()->config->uuid
        ], [
            'dispositivo_nombre' => "{$agent->platform()} {$agent->version($agent->platform())} (" . (!$agent->isDesktop() ? $agent->device() : ($agent->browser() . " " . $agent->version($agent->browser()))) . ")",
            'dispositivo_info' => [
                "device" => $agent->device(),
                "isDesktop" => $agent->isDesktop(),
                "isMobile" => $agent->isMobile(),
                "isTablet" => $agent->isTablet(),
                "isPhone" => $agent->isPhone(),
                "isRobot" => $agent->isRobot(),
                "browser"   => $agent->browser(),
                "browserVersion"   => $agent->version($agent->browser()),
                "platform"  => $agent->platform(),
                "platformVersion"  => $agent->version($agent->platform()),
            ],
            'dispositivo_plataforma' => request()->config->platform,
            'dispositivo_status' => 1
        ]);

        $session = UserSession::whereSesionConcesionario($user->id)
            ->whereSesionDispositivo(request()->config->uuid)
            ->whereSesionStatus(1)
            ->orderBy('sesion_id', 'DESC')
            ->first();

        if (!$session)
        {
            $newSession = $this->newSession($user, $data, request()->config->uuid, request()->config->platform);
            $token = $newSession['token'];
        }
        else
        {
            if ($session['ends_at'] && $session['ends_at'] < date('Y-m-d H:i:s'))
            {
                $session->update([
                    'sesion_status' => 0,
                    'sesion_logout' => date('Y-m-d H:i:s')
                ]);

                $newSession = $this->newSession($user, $data, request()->config->uuid, request()->config->platform);
                $token = $newSession['token'];
            }
            else
            {
                $token = $session['token'];
            }
        }

        return response()->json(['data' => [
            'token' => $token,
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
                'type'     => $user->type,
                'name'     => $user->name
            ]
        ]]);
    }

    public function forgotten(AuthForgotten $request)
    {
        $data = $request->validated();

        $user = User::whereConcesionarioNumero($data['username'])
            ->where('concesionario_status', '!=', 0)
            ->with(['type'])
            ->first();

        $able = $user->able();

        if (!$able->status)
        {
            return response()->json(['message' => $able->message], $able->code);
        }

        $canCommunicate = $user->canCommunicate();

        if (!$canCommunicate->status || $canCommunicate->type === 'mobile')
        {
            return response()->json(['message' => 'No se puede enviar el código de verificación ya que no cuentas con un Correo Electrónico registrado para hacerlo. Comunícate con tu Distribuidor para que él renueve tu Contraseña y te la proporcione.'], 403);
        }

        $otp = $user->createOtp();

        Notification::route('mail', $user->email)->notify(new ForgottenPassword($user, $otp));

        $renewToken = Crypt::encrypt("{$user->id}|{$user->username}");

        return response()->json(['data' => [
            'renew' => $renewToken
        ]]);
    }

    public function valid_token(AuthValidate $request)
    {
        $data = $request->validated();

        $token = Crypt::decrypt($data['token']);

        list($id, $username) = explode('|', $token);

        $exists = User::whereConcesionarioNumero($username)
            ->whereConcesionarioId($id)
            ->where('concesionario_status', '!=', 0)
            ->exists();

        if (!$exists)
        {
            return response()->json(['message' => 'Esta solicitud no es válida, intenta nuevamente lo que estas tratando de hacer.'], 401);
        }

        return response()->json();
    }

    public function code(AuthCode $request)
    {
        $data = $request->validated();

        $token = Crypt::decrypt($data['token']);

        list($id, $username) = explode('|', $token);

        $user = User::whereConcesionarioNumero($username)
            ->whereConcesionarioId($id)
            ->where('concesionario_status', '!=', 0)
            ->first();

        if (!$user)
        {
            return response()->json(['message' => 'Esta solicitud no es válida, intenta nuevamente lo que estas tratando de hacer.', ['error' => '00001']], 401);
        }

        $otp = $user->currentOtp();

        if (!$otp)
        {
            return response()->json(['message' => 'Esta solicitud no es válida, intenta nuevamente lo que estas tratando de hacer.', ['error' => '00002']], 401);
        }

        if (!password_verify($data['code'], $otp->code))
        {
            return response()->json(['message' => 'El código de verificación es incorrecto.', ['error' => '00003']], 400);
        }

        return response()->json();
    }

    public function renew(AuthRenew $request)
    {
        $data = $request->validated();

        $token = Crypt::decrypt($data['token']);

        list($id, $username) = explode('|', $token);

        $user = User::whereConcesionarioNumero($username)
            ->whereConcesionarioId($id)
            ->where('concesionario_status', '!=', 0)
            ->first();

        if (!$user)
        {
            return response()->json(['message' => 'Esta solicitud no es válida, intenta nuevamente lo que estas tratando de hacer.', ['error' => '00001']], 401);
        }

        $user->update([
            'concesionario_password' => Hash::make($data['password'])
        ]);

        $agent = new Agent();

        Device::updateOrCreate([
            'dispositivo_concesionario' => $user->id,
            'dispositivo_uuid' => request()->config->uuid
        ], [
            'dispositivo_nombre' => "{$agent->platform()} {$agent->version($agent->platform())} (" . (!$agent->isDesktop() ? $agent->device() : ($agent->browser() . " " . $agent->version($agent->browser()))) . ")",
            'dispositivo_info' => [
                "device" => $agent->device(),
                "isDesktop" => $agent->isDesktop(),
                "isMobile" => $agent->isMobile(),
                "isTablet" => $agent->isTablet(),
                "isPhone" => $agent->isPhone(),
                "isRobot" => $agent->isRobot(),
                "browser"   => $agent->browser(),
                "browserVersion"   => $agent->version($agent->browser()),
                "platform"  => $agent->platform(),
                "platformVersion"  => $agent->version($agent->platform()),
            ],
            'dispositivo_plataforma' => request()->config->platform,
            'dispositivo_status' => 1
        ]);

        $session = UserSession::whereSesionConcesionario($user->id)
            ->whereSesionDispositivo(request()->config->uuid)
            ->whereSesionStatus(1)
            ->orderBy('sesion_id', 'DESC')
            ->first();

        if (!$session)
        {
            $newSession = $this->newSession($user, $data, request()->config->uuid, request()->config->platform);
            $token = $newSession['token'];
        }
        else
        {
            if ($session['ends_at'] && $session['ends_at'] < date('Y-m-d H:i:s'))
            {
                $session->update([
                    'sesion_status' => 0,
                    'sesion_logout' => date('Y-m-d H:i:s')
                ]);

                $newSession = $this->newSession($user, $data, request()->config->uuid, request()->config->platform);
                $token = $newSession['token'];
            }
            else
            {
                $token = $session['token'];
            }
        }

        Notification::route('mail', $user->email)->notify(new RenewPassword($user));

        return response()->json(['data' => [
            'token' => $token,
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
                'type'     => $user->type,
                'name'     => $user->name
            ]
        ]]);
    }

    protected function newSession($user, $data, $uuid, $platform)
    {
        $connection = $this->getConnection($user, $platform);

        $payload = [
            'id' => $user->concesionario_id,
            'tipo' => $user->concesionario_tipo,
            'iss' => 'api.mobirock',
            'aud' => 'pdv.mobirock',
            'iat' => time(),
            'nbf' => time()
        ];

        if (empty($data['remember']))
        {
            $payload['exp'] = strtotime('+2 hours');
        }

        $token = JWT::encode($payload, config('app.key'), 'HS256');

        $session = UserSession::create([
            'sesion_concesionario' => $user->concesionario_id,
            'sesion_dispositivo' => $uuid,
            'sesion_token' => $token,
            'sesion_conexion' => $connection->conexion_id,
            'sesion_inicio' => date('Y-m-d H:i:s'),
            'sesion_duracion' => (empty($data['remember']) ? (2 * 60 * 60) : NULL),
            'sesion_finaliza' => (empty($data['remember']) ? date('Y-m-d H:i:s', strtotime('+2 hours')) : NULL),
            'sesion_plataforma' => $platform,
            'sesion_status' => 1
        ]);

        return ['token' => $token, 'data' => $session];
    }

    public function getConnection($user, $platform)
    {
        $connection = Connection::whereConexionConcesionario($user->id)
            ->whereConexionIp(request()->ip())
            ->whereConexionStatus(1)
            ->whereNull('conexion_caducada')
            ->whereNUll('conexion_bloqueada')
            ->first();

        if ($connection)
        {
            return $connection;
        }

        return Connection::create([
            'conexion_concesionario' => $user->concesionario_id,
            'conexion_nombre' => ($platform === '1' ? 'Autorizada por medio de la Web App' : 'Autorizada por medio de la Mobile App'),
            'conexion_creada' => date('Y-m-d H:i:s'),
            'conexion_ip' => request()->ip(),
            'conexion_bypass' => 1,
            'conexion_status' => 1
        ]);
    }

    public function changePassword(ChangePassword $request)
    {
        $user = request()->auth->user;

        if (!Hash::check($request->current, $user->password))
        {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 400);
        }

        $user->update([
            'concesionario_password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'La contraseña se ha actualizado correctamente.'
        ]);
    }
}
