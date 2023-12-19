<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UserExport implements FromView, ShouldAutoSize
{
    public $datas;
    
    public function __construct($props) {
        $this->datas = $props['datas'];
    }
    
    public function view(): View 
    {
        return view('export.user', [
            'datas' => $this->datas,
        ]);
    }
}
