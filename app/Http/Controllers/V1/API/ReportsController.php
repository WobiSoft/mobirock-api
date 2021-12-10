<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

use App\Exports\ReportsPurchasesExport;
use App\Exports\ReportsSalesExport;
use App\Models\Payment;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function sales(Request $request)
    {
        $user = $request->auth->parent;

        $query = Sale::orderBy('venta_id', 'DESC')
            ->whereVentaConcesionario($user->id);

        $query->with(['brand', 'created_by']);

        switch ($request->get('type'))
        {
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
                $query->whereBetween('venta_procesada', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $query->whereRaw('DATE(venta_procesada) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                break;

            case '6':
                $query->whereVentaReferencia($request->get('term'))->where('venta_procesada', '>', date('Y-m-d H:i:s', strtotime('-3 months')));
                break;
        }

        $total = floatval($query->sum('venta_importe'));

        if ($request->get('export'))
        {
            $sales = $query->get();

            $name = (date('Y/m/d/') . $user->id . '/' . 'Reporte_de_Ventas.xlsx');

            Excel::store(new ReportsSalesExport($sales, $total), $name, 's3', NULL, ['visibility' => 'private']);

            return response()->json([
                'data' => $query->paginate(10),
                'total' => $total,
                'file' => (url('reports') . '/' . $name . "?v=" . time())
            ]);
        }

        return response()->json([
            'data' => $query->paginate(10),
            'total' => $total,
        ]);
    }

    public function purchases(Request $request)
    {
        $user = $request->auth->parent;

        $query = Payment::orderBy('pago_id', 'DESC')
            ->wherePagoConcesionario($user->id);

        $queryTotal = Payment::orderBy('pago_id', 'DESC')
            ->wherePagoStatus(1)
            ->wherePagoConcesionario($user->id);

        $query->with(['transaction', 'registered_by']);

        switch ($request->get('type'))
        {
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
                $query->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $queryTotal->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                break;

            case '6':
                $query->where('pago_cdr', 'like', "%" . $request->get('term') . "%")->whereRaw('DATE(pago_registrado) > ?', [date('Y-m-d H:i:s', strtotime('-3 months'))]);
                $queryTotal->where('pago_cdr', 'like', "%" . $request->get('term') . "%")->whereRaw('DATE(pago_registrado) > ?', [date('Y-m-d H:i:s', strtotime('-3 months'))]);
                break;

            case '7':
                $query->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $queryTotal->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $query->wherePagoStatus(1);
                $queryTotal->wherePagoStatus(1);
                break;

            case '8':
                $query->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $queryTotal->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $query->wherePagoStatus(2);
                $queryTotal->wherePagoStatus(2);
                break;

            case '9':
                $query->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $queryTotal->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $query->wherePagoStatus(3);
                $queryTotal->wherePagoStatus(3);
                break;

            case '10':
                $query->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $queryTotal->whereRaw('DATE(pago_registrado) BETWEEN ? AND ?', [date('Y-m-d', strtotime($request->get('start'))), date('Y-m-d', strtotime($request->get('end')))]);
                $query->wherePagoStatus(0);
                $queryTotal->wherePagoStatus(0);
                break;
        }

        $total = floatval($queryTotal->sum('pago_monto'));

        if ($request->get('export'))
        {
            $purchases = $query->get();

            $name = (date('Y/m/d/') . $user->id . '/' . 'Reporte_de_Compras.xlsx');

            Excel::store(new ReportsPurchasesExport($purchases, $total), $name, 's3', NULL, ['visibility' => 'private']);

            return response()->json([
                'data' => $query->paginate(10),
                'total' => $total,
                'file' => (url('reports') . '/' . $name . "?v=" . time())
            ]);
        }

        return response()->json([
            'data' => $query->paginate(10),
            'total' => $total,
        ]);
    }
}
