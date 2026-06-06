<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Düşük Puanlı Yorum</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  .header { background:#c0392b; padding:24px 40px; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; }
  .body { padding:28px 40px; }
  .stars { font-size:22px; color:#f39c12; letter-spacing:2px; }
  .detail-box { background:#fff5f5; border:1px solid #fdd; border-radius:8px; padding:16px 20px; margin-top:16px; }
  .detail-row { display:flex; padding:7px 0; border-bottom:1px solid #f5e0e0; font-size:14px; }
  .detail-row:last-child { border-bottom:none; }
  .detail-label { color:#888; width:140px; flex-shrink:0; }
  .detail-value { color:#111; font-weight:600; }
  .comment-box { background:#fafafa; border-left:3px solid #c0392b; padding:12px 16px; margin-top:16px; font-size:14px; color:#444; font-style:italic; border-radius:0 6px 6px 0; }
  .btn { display:inline-block; margin-top:20px; background:#1a6b5e; color:#fff; text-decoration:none; padding:10px 22px; border-radius:6px; font-size:14px; font-weight:600; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>⚠️ Düşük Puanlı Google Yorumu</h1>
  </div>
  <div class="body">
    <p style="color:#555;font-size:14px;margin-top:0;">Google'da yeni bir düşük puanlı yorum algılandı. Yanıt vermek isteyebilirsiniz.</p>

    <div class="stars">
      @for($i = 1; $i <= 5; $i++)
        {{ $i <= $review->rating ? '★' : '☆' }}
      @endfor
      <span style="font-size:14px;color:#888;margin-left:8px;">{{ $review->rating }}/5</span>
    </div>

    <div class="detail-box">
      <div class="detail-row">
        <span class="detail-label">Yorum Sahibi</span>
        <span class="detail-value">{{ $review->author_name }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Tarih</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($review->review_time)->format('d.m.Y H:i') }}</span>
      </div>
    </div>

    @if($review->comment)
    <div class="comment-box">
      "{{ $review->comment }}"
    </div>
    @endif

    <a href="{{ url('/yonetim/yorumlar') }}" class="btn">
      Yönetim Panelinde Görüntüle
    </a>
  </div>
</div>
</body>
</html>
