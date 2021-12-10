<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsPurchasesExport implements FromView, WithTitle
{
    public $purchases;
    public $total;

    public function __construct($purchases, $total)
    {
        $this->purchases = $purchases;
        $this->total = $total;

        Log::debug($this->purchases);
    }

    public function view(): View
    {
        return view('exports.purchases', [
            'purchases' => $this->purchases
        ]);
    }

    public function title(): string
    {
        return 'Total: $' . number_format($this->total, 2, '.', ',');
    }
}
