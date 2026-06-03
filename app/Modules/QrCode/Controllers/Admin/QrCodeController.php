<?php

namespace App\Modules\QrCode\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\QrCode\Models\QrCode;
use App\Modules\QrCode\Services\QrCodeService;
use App\Modules\TablePlan\Models\Area;
use App\Modules\TablePlan\Models\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class QrCodeController extends Controller
{
    public function __construct(private QrCodeService $service) {}

    public function index()
    {
        $areas = Area::with(['tables.qrCode'])->orderBy('sort_order')->get();
        return view('admin.qrcode.index', compact('areas'));
    }

    public function generate(Table $table)
    {
        $qr = $this->service->generateForTable($table);
        return back()->with('success', "Masa {$table->table_number} için QR oluşturuldu.");
    }

    public function printSheet()
    {
        $tables = Table::where('is_active', true)
            ->with(['area', 'qrCode'])
            ->orderBy('table_number')
            ->get();

        $pdf = Pdf::loadView('admin.qrcode.print-sheet', compact('tables'))
            ->setPaper('a4');

        return $pdf->download('qr-kodlar.pdf');
    }
}
