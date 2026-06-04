<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rezervasyon Alındı</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  .header { background:#1a6b5e; padding:32px 40px; text-align:center; }
  .header h1 { margin:0; color:#fff; font-size:22px; font-weight:700; letter-spacing:.3px; }
  .header p { margin:6px 0 0; color:#a8d8d0; font-size:14px; }
  .body { padding:32px 40px; }
  .greeting { font-size:18px; font-weight:600; color:#1a1a1a; margin-bottom:8px; }
  .intro { color:#555; font-size:15px; line-height:1.6; margin-bottom:24px; }
  .detail-box { background:#f8fffe; border:1px solid #d0ede8; border-radius:10px; padding:20px 24px; margin-bottom:24px; }
  .detail-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e8f5f2; font-size:14px; }
  .detail-row:last-child { border-bottom:none; }
  .detail-label { color:#777; }
  .detail-value { color:#1a1a1a; font-weight:600; text-align:right; }
  .btn { display:block; width:fit-content; margin:0 auto 24px; background:#1a6b5e; color:#fff; text-decoration:none; padding:12px 28px; border-radius:8px; font-size:15px; font-weight:600; }
  .note { background:#fffbf0; border-left:4px solid #f59e0b; padding:12px 16px; border-radius:0 8px 8px 0; font-size:13px; color:#92400e; margin-bottom:24px; }
  .footer { background:#f8f8f8; padding:20px 40px; text-align:center; font-size:12px; color:#999; }
  .footer a { color:#1a6b5e; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>🌊 Müdavim Şef Restaurant</h1>
    <p>Palamut Bükü, Datça</p>
  </div>
  <div class="body">
    <div class="greeting">Merhaba {{ $reservation->guest_name }},</div>
    <p class="intro">
      Rezervasyon talebiniz başarıyla alındı. Restoranımız rezervasyonunuzu onayladıktan sonra size bilgi verecektir.
    </p>

    <div class="detail-box">
      <div class="detail-row">
        <span class="detail-label">Tarih</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d.m.Y') }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Saat</span>
        <span class="detail-value">{{ substr($reservation->arrival_time, 0, 5) }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Kişi Sayısı</span>
        <span class="detail-value">{{ $reservation->guest_count }} kişi</span>
      </div>
      @if($reservation->table)
      <div class="detail-row">
        <span class="detail-label">Masa</span>
        <span class="detail-value">{{ $reservation->table->table_number }}</span>
      </div>
      @endif
      @if($reservation->special_requests)
      <div class="detail-row">
        <span class="detail-label">Notunuz</span>
        <span class="detail-value">{{ $reservation->special_requests }}</span>
      </div>
      @endif
    </div>

    <a href="{{ url('/rezervasyon/' . $reservation->reservation_uuid) }}" class="btn">
      Rezervasyonumu Görüntüle
    </a>

    <div class="note">
      Rezervasyonunuzu iptal etmek isterseniz yukarıdaki bağlantıdan yapabilirsiniz.
    </div>
  </div>
  <div class="footer">
    <p>Müdavim Şef Restaurant &bull; Palamut Bükü, Datça</p>
    <p><a href="https://mudavimpalamutbuku.com">mudavimpalamutbuku.com</a></p>
  </div>
</div>
</body>
</html>
