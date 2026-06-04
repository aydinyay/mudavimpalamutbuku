<?php

namespace App\Modules\QrCode\Services;

use App\Modules\QrCode\Models\QrCode;
use App\Modules\TablePlan\Models\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QrCodeService
{
    public function generateForTable(Table $table): QrCode
    {
        $url = url("/menu/masa/{$table->table_code}");

        $qr = QrCode::updateOrCreate(
            ['target_type' => 'table', 'target_id' => $table->id],
            ['url' => $url, 'is_active' => true],
        );

        $svg = QrCodeGenerator::format('svg')
            ->size(400)
            ->margin(2)
            ->generate($url);

        $qr->update([
            'svg_content'      => (string) $svg,
            'last_generated_at'=> now(),
        ]);

        return $qr;
    }
}
