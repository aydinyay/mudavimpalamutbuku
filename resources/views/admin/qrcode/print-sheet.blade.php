<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR Kodlar — Müdavim Şef</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 10px; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .qr-item { border: 1px solid #ddd; border-radius: 8px; padding: 12px; text-align: center; page-break-inside: avoid; }
        .qr-item img { width: 140px; height: 140px; }
        .table-name { font-weight: bold; font-size: 14px; margin-top: 6px; }
        .table-area { font-size: 11px; color: #666; }
        .url-text { font-size: 9px; color: #999; word-break: break-all; margin-top: 4px; }
    </style>
</head>
<body>
    <h1>Müdavim Şef Restaurant — Masa QR Kodları</h1>
    <div class="grid">
        @foreach($tables as $table)
        <div class="qr-item">
            @if($table->qrCode && $table->qrCode->image_path)
                <img src="{{ public_path($table->qrCode->image_path) }}" alt="QR">
            @else
                <div style="width:140px;height:140px;background:#eee;margin:0 auto;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:10px;color:#999;">QR yok</span>
                </div>
            @endif
            <div class="table-name">{{ $table->table_number }}</div>
            <div class="table-area">{{ $table->area->name_tr }}</div>
            @if($table->qrCode)
            <div class="url-text">{{ $table->qrCode->url }}</div>
            @endif
        </div>
        @endforeach
    </div>
</body>
</html>
