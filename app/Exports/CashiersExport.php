<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class CashiersExport implements FromView, WithTitle
{
    public $cashiers;

    public function __construct($cashiers)
    {
        $this->cashiers = $cashiers;

        Log::debug($this->cashiers);
    }

    public function view(): View
    {
        return view('exports.cashiers', [
            'cashiers' => $this->cashiers
        ]);
    }

    public function title(): string
    {
        return 'Cajeros';
    }
}
