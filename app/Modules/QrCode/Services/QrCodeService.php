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

        $dir = public_path('qr-codes');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = "table-{$table->table_code}.png";
        $path     = "{$dir}/{$filename}";

        QrCodeGenerator::format('png')
            ->size(400)
            ->margin(2)
            ->generate($url, $path);

        $qr->update([
            'image_path'       => "/qr-codes/{$filename}",
            'last_generated_at'=> now(),
        ]);

        return $qr;
    }
}
