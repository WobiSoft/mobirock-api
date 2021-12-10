<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Method;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function accountsBymethod(Request $request, Method $method)
    {
        $user = $request->auth->user;

        $accounts = Account::select(['cuenta_id', 'cuenta_banco', 'cuenta_digitos'])
            ->whereCuentaConcesionario($user->getParent())
            ->whereIn('cuenta_forma', [$method->cuenta_forma, 4])
            ->with(['bank' => function ($query)
            {
                $query->select(['banco_id', 'banco_nombre']);
            }])
            ->get();

        $accounts->each(function ($account)
        {
            $account->bank->setAppends(['name']);
            $account->setAppends(['id', 'digits']);
        });

        return response()->json(['data' => $accounts]);
    }
}
