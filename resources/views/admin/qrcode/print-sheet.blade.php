<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR Kodlar — Müdavim</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #fff; padding: 20px; }

        .no-print { text-align: center; padding: 16px; background: #f8f9fa; margin-bottom: 20px; border-radius: 8px; }
        .no-print button { background: #1a6b5e; color: #fff; border: none; padding: 10px 28px; border-radius: 6px; font-size: 15px; cursor: pointer; margin-right: 8px; }
        .no-print .close-btn { background: #6c757d; }

        h1 { font-size: 15px; text-align: center; margin-bottom: 16px; color: #333; }

        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

        .qr-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 14px 10px;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .qr-item svg, .qr-item img { width: 150px; height: 150px; display: block; margin: 0 auto; }
        .table-name { font-weight: bold; font-size: 13px; margin-top: 8px; }
        .table-area { font-size: 10px; color: #666; margin-top: 2px; }
        .url-text { font-size: 8px; color: #999; word-break: break-all; margin-top: 4px; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
            .grid { gap: 10px; }
            @page { size: A4; margin: 1cm; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨️ Yazdır / PDF Olarak Kaydet</button>
    <button class="close-btn" onclick="window.close()">Kapat</button>
    <p style="font-size:12px;color:#666;margin-top:8px;">
        PDF kaydetmek için: Yazdır → Yazıcı olarak <strong>"PDF Olarak Kaydet"</strong> seç
    </p>
</div>

<h1>Müdavim Restaurant — Masa QR Kodları</h1>

<div class="grid">
    @foreach($tables as $table)
    <div class="qr-item">
        @if($table->qrCode && $table->qrCode->svg_content)
            {!! $table->qrCode->svg_content !!}
        @else
            <div style="width:150px;height:150px;background:#eee;display:flex;align-items:center;justify-content:center;margin:0 auto;">
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

    {{-- Google Yorum QR --}}
    <div class="qr-item" style="border:2px solid #4285F4;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https%3A%2F%2Fg.page%2Fr%2FCd4zYQe_40RuEBM%2Freview"
             width="150" height="150" alt="Google Yorum QR" style="width:150px;height:150px;">
        <div class="table-name" style="color:#4285F4;">★ Google Yorum</div>
        <div class="table-area">Deneyiminizi paylaşın</div>
        <div class="url-text">g.page/r/Cd4zYQe_40RuEBM/review</div>
    </div>
</div>

<script>
    // Sayfa yüklenince otomatik print diyalogu aç
    window.addEventListener('load', function() {
        setTimeout(function(){ window.print(); }, 600);
    });
</script>
</body>
</html>
