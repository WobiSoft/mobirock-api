<?php

namespace App\Http\Controllers\V1\API;

use App\Exports\CashiersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CashierStore;
use App\Http\Requests\V1\CashierUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CashiersController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->auth->parent;

        $query = User::select([
            'concesionario_id',
            'concesionario_numero',
            'concesionario_primer_nombre',
            'concesionario_segundo_nombre',
            'concesionario_apellido_paterno',
            'concesionario_apellido_materno',
            'concesionario_email',
            'concesionario_movil',
            'concesionario_telefono',
            'concesionario_creado'
        ])
            ->whereConcesionarioTipo(6)
            ->whereConcesionarioPadre($parent->id)
            ->whereConcesionarioStatus(1)
            ->orderBy('concesionario_id', 'DESC')
            ->with(['config' => function ($query)
            {
                $query->select(['config_concesionario', 'config_bloqueado']);
            }]);

        switch ($request->get('type'))
        {
            case '2':
                $query->whereRaw("CONCAT_WS(' ',
                    concesionario_primer_nombre,
                    concesionario_segundo_nombre,
                    concesionario_apellido_paterno,
                    concesionario_apellido_materno,
                    concesionario_primer_nombre,
                    concesionario_apellido_paterno,
                    concesionario_segundo_nombre,
                    concesionario_apellido_materno
                ) LIKE ?", ["%" . $request->get('term') . "%"]);
                break;

            case '3':
                $query->whereConcesionarioId($request->get('term'));
                break;
        }

        if ($request->get('export'))
        {
            $cashiers = $query->get();

            $name = (date('Y/m/d/') . $parent->id . '/' . 'Reporte_de_Cajeros.xlsx');

            Excel::store(new CashiersExport($cashiers), $name, 's3', NULL, ['visibility' => 'private']);

            return response()->json([
                'data' => $query->paginate(10),
                'file' => (url('reports') . '/' . $name . "?v=" . time())
            ]);
        }

        $cashiers = $query->paginate(10);

        $cashiers->each(function ($cashier)
        {
            $cashier->config->setAppends(['blocked']);
            $cashier->setAppends([
                'id',
                'username',
                'first_name',
                'second_name',
                'first_surname',
                'second_surname',
                'name',
                'email',
                'phone',
                'mobile',
                'created_at',
            ]);
        });

        return response()->json(['data' => $cashiers]);
    }

    public function store(CashierStore $request)
    {
        $user = request()->auth->user;

        $data = $request->validated();

        $cashier = new User();

        $cashier->concesionario_padre = $user->id;
        $cashier->concesionario_numero = $cashier->uniqueUsername();
        $cashier->concesionario_tipo = 6;
        $cashier->concesionario_password = password_hash($data['password'], PASSWORD_BCRYPT);
        $cashier->concesionario_primer_nombre = (!empty($data['first_name']) ? Str::title($data['first_name']) : NULL);
        $cashier->concesionario_segundo_nombre = (!empty($data['second_name']) ? Str::title($data['second_name']) : NULL);
        $cashier->concesionario_apellido_paterno = (!empty($data['first_surname']) ? Str::title($data['first_surname']) : NULL);
        $cashier->concesionario_apellido_materno = (!empty($data['second_surname']) ? Str::title($data['second_surname']) : NULL);
        $cashier->concesionario_email = $data['email'];
        $cashier->concesionario_movil = $data['mobile'];
        $cashier->concesionario_telefono = $data['phone'];
        $cashier->concesionario_creado_por = $user->id;
        $cashier->concesionario_creado = date('Y-m-d H:i:s');
        $cashier->concesionario_status = 1;

        $cashier->save();

        $cashier->config()->create([
            'config_concesionario' => $cashier->id,
            'config_bloqueado' => 0
        ]);

        return response()->json(['message' => 'Cajero creado exitosamente.']);
    }

    public function show(User $user)
    {
        //
    }

    public function update(CashierUpdate $request, User $user)
    {
        if ($user->parent->id !== request()->auth->user->id)
        {
            return response()->json(['message' => 'Este Cajero no existe.'], 404);
        }

        $data = $request->validated();

        $user->update([
            'concesionario_primer_nombre' => Str::title($data['first_name']),
            'concesionario_segundo_nombre' => (!empty($data['second_name']) ? Str::title($data['second_name']) : NULL),
            'concesionario_apellido_paterno' => Str::title($data['first_surname']),
            'concesionario_apellido_materno' => (!empty($data['second_surname']) ? Str::title($data['second_surname']) : NULL),
            'concesionario_email' => $data['email'],
            'concesionario_movil' => $data['mobile'],
            'concesionario_telefono' => $data['phone'],
        ]);

        return response()->json(['message' => 'El Cajero ha sido actualizado exitosamente.']);
    }

    public function updateBlocked(Request $request, User $user)
    {
        if ($user->parent->id !== $request->auth->user->id)
        {
            return response()->json(['message' => 'Este Cajero no existe.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'blocked' => 'required|in:1,0'
        ], [
            'blocked.required' => 'Debes establecer el Estatus del Cajero.',
            'blocked.in' => 'Este Estatus no es válido.'
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        if (boolval($data['blocked']) === $user->config->blocked)
        {
            return response()->json(['message' => 'El Estatus del Cajero no puede ser modificado.'], 422);
        }

        $user->config->update(['config_bloqueado' => $data['blocked']]);

        return response()->json(['message' => 'El Estatus del Cajero ha sido modificado exitosamente.']);
    }

    public function updateStatus(Request $request, User $user)
    {
        if ($user->parent->id !== $request->auth->user->id)
        {
            return response()->json(['message' => 'Este Cajero no existe.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0'
        ], [
            'status.required' => 'Debes establecer el Estatus del Cajero.',
            'status.in' => 'Este Estatus no es válido.'
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        if (intval($data['status']) === $user->status->id)
        {
            return response()->json(['message' => 'Este Cajero no existe.'], 404);
        }

        $user->update(['concesionario_status' => $data['status']]);

        return response()->json(['message' => 'El Cajero ha sido eliminado exitosamente.']);
    }

    public function updatePassword(Request $request, User $user)
    {
        if ($user->parent->id !== $request->auth->user->id)
        {
            return response()->json(['message' => 'Este Cajero no existe.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed'
        ], [
            'status.required' => 'Debes enviar la Nueva Contraseña.',
            'status.confirmed' => 'Las Contraseñas no coinciden.'
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        $user->update(['concesionario_password' => password_hash($data['password'], PASSWORD_BCRYPT)]);

        return response()->json(['message' => 'La Contraseña del Cajero ha sido actualizada exitosamente.']);
    }
}
