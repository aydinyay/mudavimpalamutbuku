<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Hızlı Rezervasyon — Müdavim</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #0f172a;
    color: #f1f5f9;
    min-height: 100vh;
    padding: 20px 16px 40px;
}

.logo {
    text-align: center;
    margin-bottom: 24px;
}
.logo span {
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: .06em;
    color: #94a3b8;
    text-transform: uppercase;
}

h1 {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 6px;
    color: #f1f5f9;
}
.subtitle {
    text-align: center;
    font-size: .82rem;
    color: #64748b;
    margin-bottom: 28px;
}

.card {
    background: #1e293b;
    border-radius: 16px;
    padding: 24px;
    max-width: 480px;
    margin: 0 auto;
    border: 1px solid #334155;
}

.field {
    margin-bottom: 16px;
}
label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: 6px;
}
label .req { color: #f87171; }

input, select, textarea {
    width: 100%;
    background: #0f172a;
    border: 2px solid #334155;
    border-radius: 10px;
    color: #f1f5f9;
    font-size: 1rem;
    padding: 12px 14px;
    transition: border-color .2s;
    -webkit-appearance: none;
}
input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #3b82f6;
}
input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); }
textarea { resize: none; height: 80px; }

.row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.source-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.source-btn {
    background: #0f172a;
    border: 2px solid #334155;
    border-radius: 10px;
    color: #94a3b8;
    padding: 10px 6px;
    font-size: .82rem;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
}
.source-btn.active {
    background: #1d4ed8;
    border-color: #3b82f6;
    color: #fff;
}
input[name="source"] { display: none; }

.btn-submit {
    width: 100%;
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 16px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    margin-top: 8px;
    transition: background .2s, transform .1s;
}
.btn-submit:active { transform: scale(.98); }
.btn-submit:hover  { background: #15803d; }

.btn-list {
    display: block;
    text-align: center;
    margin-top: 16px;
    color: #64748b;
    text-decoration: none;
    font-size: .85rem;
}
.btn-list:hover { color: #94a3b8; }

.alert-success {
    background: #14532d;
    border: 1px solid #16a34a;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: .9rem;
    color: #86efac;
    max-width: 480px;
    margin: 0 auto 20px;
}
.alert-error {
    background: #450a0a;
    border: 1px solid #ef4444;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: .85rem;
    color: #fca5a5;
    max-width: 480px;
    margin: 0 auto 20px;
}
.field-error { color: #f87171; font-size: .78rem; margin-top: 4px; }
</style>
</head>
<body>

<div class="logo">
    <span>Müdavim Restaurant</span>
</div>

<h1>⚡ Hızlı Rezervasyon</h1>
<p class="subtitle">Telefon veya yüz yüze gelen rezervasyonlar için</p>

@if(session('success'))
<div class="alert-success">✓ {{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert-error">
    @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
</div>
@endif

<form method="POST" action="{{ route('admin.reservations.store') }}">
@csrf

    <div class="card">

        {{-- Ad Soyad --}}
        <div class="field">
            <label>Ad Soyad <span class="req">*</span></label>
            <input type="text" name="guest_name" value="{{ old('guest_name') }}"
                   placeholder="Ahmet Yılmaz" autocomplete="name" autofocus>
        </div>

        {{-- Telefon --}}
        <div class="field">
            <label>Telefon</label>
            <input type="tel" name="guest_phone" value="{{ old('guest_phone', '0') }}"
                   placeholder="0555 123 45 67" autocomplete="tel">
        </div>

        {{-- Tarih + Saat --}}
        <div class="row2">
            <div class="field">
                <label>Tarih <span class="req">*</span></label>
                <input type="date" name="reservation_date"
                       value="{{ old('reservation_date', now()->format('Y-m-d')) }}"
                       min="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="field">
                <label>Saat <span class="req">*</span></label>
                <input type="time" name="arrival_time"
                       value="{{ old('arrival_time', '19:00') }}"
                       step="900">
            </div>
        </div>

        {{-- Kişi Sayısı --}}
        <div class="field">
            <label>Kişi Sayısı <span class="req">*</span></label>
            <select name="guest_count">
                @for($i = 1; $i <= 20; $i++)
                <option value="{{ $i }}" {{ old('guest_count', 2) == $i ? 'selected' : '' }}>
                    {{ $i }} kişi
                </option>
                @endfor
            </select>
        </div>

        {{-- Kaynak --}}
        <div class="field">
            <label>Nasıl Geldi? <span class="req">*</span></label>
            <input type="hidden" name="source" id="sourceVal" value="{{ old('source', 'phone') }}">
            <div class="source-grid">
                <div class="source-btn {{ old('source', 'phone') == 'phone'  ? 'active' : '' }}" data-val="phone">📞 Telefon</div>
                <div class="source-btn {{ old('source', 'phone') == 'walkin' ? 'active' : '' }}" data-val="walkin">🚶 Yüz Yüze</div>
                <div class="source-btn {{ old('source', 'phone') == 'boat'   ? 'active' : '' }}" data-val="boat">⛵ Tekne</div>
            </div>
        </div>

        {{-- Not --}}
        <div class="field">
            <label>Not (opsiyonel)</label>
            <textarea name="special_requests" placeholder="Doğum günü, özel istek, masa tercihi...">{{ old('special_requests') }}</textarea>
        </div>

        {{-- Hidden alanlar --}}
        <input type="hidden" name="guest_email" value="">
        <input type="hidden" name="internal_notes" value="Hızlı giriş — Emre">

        <button type="submit" class="btn-submit">✓ Rezervasyonu Kaydet</button>
    </div>

</form>

<a href="{{ route('admin.reservations.index') }}" class="btn-list">← Tüm Rezervasyonlara Dön</a>

<script>
document.querySelectorAll('.source-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.source-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('sourceVal').value = btn.dataset.val;
    });
});
</script>

</body>
</html>
