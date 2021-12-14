<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ProfileUpdate;
use App\Models\User;
use App\Models\UserPush;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function storePush(Request $request)
    {
        $user = $request->auth->user;
        $uuid = $request->config->uuid;
        $token = $request->post('token');

        UserPush::updateOrCreate([
            'user_id' => $user->id,
            'uuid' => $uuid,
        ], [
            'token' => $token,
        ]);

        return response()->json([
            'message' => 'Push Token registrado correctamente.',
        ]);
    }

    public function show(Request $request)
    {
        $user = $request->auth->user;

        $user->business;

        return response()->json(['data' => $user]);
    }

    public function update(ProfileUpdate $request)
    {
        $user = request()->auth->user;

        $userData = $request->validated();

        $businessData = ($userData['business'] ?? NULL);

        unset($userData['business']);

        if (in_array($user->type->id, [1, 3, 5, 7, 8]))
        {
            $validator = Validator::make($businessData, [
                'name' => 'required',
                'street' => 'nullable',
                'street_number' => 'nullable',
                'apartment_number' => 'nullable',
                'complement_1' => 'nullable',
                'complement_2' => 'nullable',
                'postal_code' => 'nullable',
                'settlement' => 'nullable',
                'locality' => 'required',
                'municipality' => 'required',
                'state' => 'required',
            ], [
                'name.required' => 'El nombre de la empresa es obligatorio',
                'locality.required' => 'La ciudad o localidad es obligatoria',
                'municipality.required' => 'El municipio o alcaldía es obligatorio',
                'state.required' => 'El estado es obligatorio',
            ]);

            if ($validator->fails())
            {
                return response()->json(['message' => $validator->errors()->first()], 422);
            }

            $businessData = $validator->validated();
        }

        $user->saveProfileState($userData, $businessData);

        $user->update([
            'concesionario_primer_nombre'    => Str::title($userData['first_name']),
            'concesionario_segundo_nombre'   => $userData['second_name'] ? Str::title($userData['second_name']) : NULL,
            'concesionario_apellido_paterno' => Str::title($userData['first_surname']),
            'concesionario_apellido_materno' => $userData['second_surname'] ? Str::title($userData['second_surname']) : NULL,
            'concesionario_email'            => $userData['email'],
            'concesionario_movil'            => $userData['mobile'],
            'concesionario_telefono'         => $userData['phone'],
        ]);

        if (!empty($businessData))
        {
            $user->business->update([
                'negocio_nombre'                  => Str::title($businessData['name']),
                'negocio_domicilio_calle'         => $businessData['street'] ? Str::title($businessData['street']) : NULL,
                'negocio_domicilio_no_ext'        => $businessData['street_number'] ? Str::title($businessData['street_number']) : NULL,
                'negocio_domicilio_no_int'        => $businessData['apartment_number'] ? Str::title($businessData['apartment_number']) : NULL,
                'negocio_domicilio_entre_calle_1' => $businessData['complement_1'] ? Str::title($businessData['complement_1']) : NULL,
                'negocio_domicilio_entre_calle_2' => $businessData['complement_2'] ? Str::title($businessData['complement_2']) : NULL,
                'negocio_domicilio_colonia'       => $businessData['settlement'] ? Str::title($businessData['settlement']) : NULL,
                'negocio_domicilio_estado'        => $businessData['state'],
                'negocio_domicilio_municipio'     => Str::title($businessData['municipality']),
                'negocio_domicilio_localidad'     => Str::title($businessData['locality']),
                'negocio_domicilio_cp'            => $businessData['postal_code'],
            ]);
        }

        return response()->json(['message' => 'Perfil actualizado correctamente']);
    }

    public function destroy(User $user)
    {
        //
    }
}
