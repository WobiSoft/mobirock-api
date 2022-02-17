<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TestsController extends Controller
{
    public function svbalance(Request $request)
    {
        $provider = Provider::find(29);
        $provider_config = config('providers.' . $provider->class);
        $provider_service = app(('\\App\\Services\\Providers\\' . ucfirst(Str::camel($provider->class))));
        $provider_service->set($provider, $provider_config);

        $provider_balance = $provider_service->balance()->balance;

        return response()->json([
            'message' => 'Saldo en ' . $provider->name . ':',
            'balance' => $provider_balance
        ]);
    }
}
