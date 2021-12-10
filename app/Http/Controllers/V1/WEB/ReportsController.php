<?php

namespace App\Http\Controllers\V1\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    public function get(Request $request)
    {
        try
        {
            return Storage::download("{$request->year}/{$request->month}/{$request->day}/{$request->id}/{$request->file}");
        }
        catch (\Exception $e)
        {
            return view('errors.file-not-found');
        }
    }
}
