<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsSalesExport implements FromView, WithTitle
{
    public $sales;
    public $total;

    public function __construct($sales, $total)
    {
        $this->sales = $sales;
        $this->total = $total;

        Log::debug($this->sales);
    }

    public function view(): View
    {
        return view('exports.sales', [
            'sales' => $this->sales
        ]);
    }

    public function title(): string
    {
        return 'Total: $' . number_format($this->total, 2, '.', ',');
    }
}
