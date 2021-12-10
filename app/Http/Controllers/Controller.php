<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function validatedFile($file)
    {
        return Str::startsWith($file, 'data:');
    }

    public function saveFile($file, $path)
    {
        $explodedData = explode('ata:', $file);

        list($mimeType, $base64Data) = explode(';base64,', $explodedData[1]);

        switch ($mimeType)
        {
            case 'image/webp':
                $extension = 'webp';
                break;

            case 'image/png':
                $extension = 'png';
                break;

            default:
                $extension = 'jpg';
                break;
        }

        $fileName = $path . '.' . $extension;

        Storage::put($fileName, base64_decode($base64Data), 'public');

        return Storage::url($fileName);
    }
}
