<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandProduct;
use App\Models\ProviderDispatcher;
use App\Models\ProviderProduct;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    public function index()
    {
        //
    }

    public function indexByType(Request $request, $type)
    {
        switch ($type)
        {
            case 'prepaid':
                $type = 1;
                break;

            case 'packages':
                $type = 2;
                break;
        }

        $brands = Brand::whereMarcaTipo($type)->whereMarcaStatus(1)->get();

        return response()->json(['data' => $brands]);
    }

    public function store(Request $request)
    {
        //
    }


    public function show(Brand $brand)
    {
        //
    }

    public function update(Request $request, Brand $brand)
    {
        //
    }

    public function destroy(Brand $brand)
    {
        //
    }

    public function productsByBrand(Request $request, Brand $brand)
    {
        $dispatcher = ProviderDispatcher::whereDispatcherMarca($brand->id)
            ->first();

        $products = ProviderProduct::select(['producto_id', 'producto_marca', 'producto_importe'])
            ->whereProductoProveedor($dispatcher->provider_id)
            ->whereProductoMarca($brand->id)
            ->whereProductoStatus(1)
            ->orderBy('producto_importe', 'ASC')
            ->get();

        $products->each(function ($product)
        {
            $info = BrandProduct::select(['producto_incluye', 'producto_vigencia'])
                ->whereProductoMarca($product->brand_id)
                ->whereProductoMonto($product->amount)
                ->first();

            $info->setAppends(['includes', 'expires_at'])->toArray();
            $product->setAppends(['id', 'amount'])->toArray();

            $including = explode('|', $info['includes']);
            $includes = [];

            foreach ($including as $include)
            {
                $includes[] = trim($include);
            }

            $info['including'] = $includes;

            $product['info'] = $info;
        });

        return response()->json(['data' => $products]);
    }
}
