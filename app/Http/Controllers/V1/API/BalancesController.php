<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BalancesController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->auth->parent;

        return response()->json([
            'data' => $user->config->balance_tae
        ]);
    }
}
