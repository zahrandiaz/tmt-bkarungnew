<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProfitAndLossExport implements FromView, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Menggunakan template Blade untuk membuat struktur laporan.
     */
    public function view(): View
    {
        return view('reports.exports.profit_and_loss', [
            'data' => $this->data
        ]);
    }
}