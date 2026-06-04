<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Yeni Rezervasyon</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  .header { background:#c0392b; padding:24px 40px; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; }
  .body { padding:28px 40px; }
  .detail-box { background:#fafafa; border:1px solid #eee; border-radius:8px; padding:16px 20px; }
  .detail-row { display:flex; padding:7px 0; border-bottom:1px solid #f0f0f0; font-size:14px; }
  .detail-row:last-child { border-bottom:none; }
  .detail-label { color:#888; width:140px; flex-shrink:0; }
  .detail-value { color:#111; font-weight:600; }
  .btn { display:inline-block; margin-top:20px; background:#c0392b; color:#fff; text-decoration:none; padding:10px 22px; border-radius:6px; font-size:14px; font-weight:600; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>🔔 Yeni Rezervasyon Geldi</h1>
  </div>
  <div class="body">
    <div class="detail-box">
      <div class="detail-row">
        <span class="detail-label">Ad Soyad</span>
        <span class="detail-value">{{ $reservation->guest_name }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Telefon</span>
        <span class="detail-value">{{ $reservation->guest_phone }}</span>
      </div>
      @if($reservation->guest_email)
      <div class="detail-row">
        <span class="detail-label">E-posta</span>
        <span class="detail-value">{{ $reservation->guest_email }}</span>
      </div>
      @endif
      <div class="detail-row">
        <span class="detail-label">Tarih / Saat</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d.m.Y') }} — {{ substr($reservation->arrival_time, 0, 5) }}</span>
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
        <span class="detail-label">Özel İstek</span>
        <span class="detail-value">{{ $reservation->special_requests }}</span>
      </div>
      @endif
      <div class="detail-row">
        <span class="detail-label">Kaynak</span>
        <span class="detail-value">{{ $reservation->source ?? 'website' }}</span>
      </div>
    </div>

    <a href="{{ url('/yonetim/rezervasyonlar/' . $reservation->id) }}" class="btn">
      Yönetim Panelinde Görüntüle
    </a>
  </div>
</div>
</body>
</html>
