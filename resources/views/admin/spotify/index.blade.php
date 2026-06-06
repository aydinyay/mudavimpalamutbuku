@extends('layouts.admin')

@section('title', 'Spotify Müzik')
@section('page_title', 'Spotify Müzik Entegrasyonu')

@push('styles')
<style>
.spotify-status-card {
    border-radius:14px; padding:24px;
    background: linear-gradient(135deg, #191414, #1a1a2e);
    color:#fff; position:relative; overflow:hidden;
}
.spotify-status-card::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:200px; height:200px; border-radius:50%;
    background:rgba(29,185,84,.15); pointer-events:none;
}
.spotify-logo { color:#1DB954; font-size:2.5rem; }
.conn-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 14px; border-radius:20px; font-size:.78rem; font-weight:700;
}
.conn-badge.connected { background:rgba(29,185,84,.2); color:#1DB954; border:1px solid rgba(29,185,84,.3); }
.conn-badge.disconnected { background:rgba(255,100,100,.15); color:#ff6464; border:1px solid rgba(255,100,100,.2); }
.sp-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
.sp-dot.on { background:#1DB954; animation:pulse 1.5s infinite; }
.sp-dot.off { background:#666; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.5} }

.setting-card { border-radius:12px; background:#fff; border:1px solid #f0f0f0; padding:20px; margin-bottom:16px; }
.setting-card h6 { font-size:.72rem; font-weight:700; color:#aaa; text-transform:uppercase; letter-spacing:.07em; margin-bottom:14px; }

.toggle-wrap { display:flex; align-items:center; gap:12px; }
.toggle-switch { position:relative; width:48px; height:26px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider {
    position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0;
    background:#ddd; transition:.3s; border-radius:26px;
}
.toggle-slider:before {
    position:absolute; content:''; height:20px; width:20px;
    left:3px; bottom:3px; background:#fff; transition:.3s; border-radius:50%;
}
input:checked + .toggle-slider { background:#1DB954; }
input:checked + .toggle-slider:before { transform:translateX(22px); }
</style>
@endpush

@section('content')

<div class="row g-3">

    {{-- Status Card --}}
    <div class="col-12">
        <div class="spotify-status-card">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <i class="bi bi-spotify spotify-logo"></i>
                        <div>
                            <div style="font-size:1.1rem;font-weight:700;">Spotify Müzik Motoru</div>
                            <div style="font-size:.78rem;opacity:.6;">Dinamik müzik akışı — Şu An, Az Önce, Sırada</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        @if($connected)
                        <span class="conn-badge connected"><span class="sp-dot on"></span> Spotify Bağlı</span>
                        @else
                        <span class="conn-badge disconnected"><span class="sp-dot off"></span> Bağlı Değil</span>
                        @endif

                        @if($setting->spotify_enabled && $connected)
                        <span class="conn-badge connected"><span class="sp-dot on"></span> Widget Aktif</span>
                        @else
                        <span class="conn-badge disconnected"><span class="sp-dot off"></span> Widget Pasif</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($connected)
                    <a href="{{ route('admin.spotify.auth') }}"
                       class="btn btn-sm" style="background:#1DB954;color:#fff;border-radius:20px;font-weight:600;">
                        <i class="bi bi-arrow-clockwise me-1"></i>Yeniden Bağlan
                    </a>
                    <form method="POST" action="{{ route('admin.spotify.disconnect') }}" class="d-inline"
                          onsubmit="return confirm('Spotify bağlantısı kesilecek. Emin misiniz?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" style="border-radius:20px;">
                            <i class="bi bi-x-circle me-1"></i>Bağlantıyı Kes
                        </button>
                    </form>
                    @else
                    <a href="{{ route('admin.spotify.auth') }}"
                       class="btn btn-sm" style="background:#1DB954;color:#fff;border-radius:20px;font-weight:600;padding:8px 20px;">
                        <i class="bi bi-spotify me-1"></i>Spotify'a Bağlan
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($connected)

    {{-- Aktif/Pasif Toggle --}}
    <div class="col-md-6">
        <div class="setting-card">
            <h6>Widget Kontrolü</h6>
            <form method="POST" action="{{ route('admin.spotify.toggle') }}">
                @csrf
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div style="font-weight:600;font-size:.88rem;">Spotify Widget</div>
                        <div style="font-size:.72rem;color:#aaa;">Müzik bilgisini sitede göster</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="enabled" value="1"
                               {{ $setting->spotify_enabled ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.spotify.settings') }}">
                @csrf
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div style="font-size:.82rem;font-weight:600;">Web Sitesinde Göster</div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="spotify_widget_on_website" value="1"
                               {{ $setting->spotify_widget_on_website ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div style="font-size:.82rem;font-weight:600;">QR Menüde Göster</div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="spotify_widget_on_menu" value="1"
                               {{ $setting->spotify_widget_on_menu ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <hr style="border-color:#f0f0f0;margin:14px 0;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div style="font-size:.82rem;font-weight:600;">Ambiyans Sayfası Aktif</div>
                        <div style="font-size:.7rem;color:#aaa;">mudavimpalamutbuku.com/ambiyans</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="ambiance_page_active" value="1"
                               {{ ($setting->ambiance_page_active ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <button class="btn btn-sm btn-outline-secondary w-100" style="border-radius:8px;">
                    Kaydet
                </button>
            </form>
        </div>
    </div>

    {{-- Token Durumu --}}
    <div class="col-md-6">
        <div class="setting-card">
            <h6>Token Durumu</h6>
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-key text-success"></i>
                <span style="font-size:.82rem;">Access Token</span>
                @if($setting->spotify_token_expires_at)
                <span class="badge" style="background:#e6f4f1;color:#1a6b5e;font-size:.65rem;">
                    {{ \Carbon\Carbon::parse($setting->spotify_token_expires_at)->diffForHumans() }} sürüyor
                </span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-arrow-repeat text-success"></i>
                <span style="font-size:.82rem;">Refresh Token</span>
                <span class="badge" style="background:#e6f4f1;color:#1a6b5e;font-size:.65rem;">Kayıtlı</span>
            </div>
            <button onclick="manualRefresh()" class="btn btn-sm btn-outline-secondary w-100" style="border-radius:8px;">
                <i class="bi bi-arrow-clockwise me-1"></i>Token'ı Manuel Yenile
            </button>
            <div id="refresh-result" class="mt-2 text-center" style="font-size:.75rem;"></div>
        </div>
    </div>

    @endif

    {{-- Fallback Playlist --}}
    <div class="col-12">
        <div class="setting-card">
            <h6>Fallback — Müzik Kapalıyken</h6>
            <p style="font-size:.8rem;color:#888;margin-bottom:14px;">
                Spotify aktif değilken widget boş görünmez, bunun yerine bu playlist bilgisini gösterir.
            </p>
            <form method="POST" action="{{ route('admin.spotify.settings') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label style="font-size:.75rem;font-weight:600;color:#666;">Playlist Adı</label>
                        <input type="text" name="spotify_fallback_playlist_name" class="form-control form-control-sm"
                               value="{{ $setting->spotify_fallback_playlist_name }}"
                               placeholder="Müdavim Favori Listesi">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size:.75rem;font-weight:600;color:#666;">Spotify Playlist URL</label>
                        <input type="url" name="spotify_fallback_playlist_url" class="form-control form-control-sm"
                               value="{{ $setting->spotify_fallback_playlist_url }}"
                               placeholder="https://open.spotify.com/playlist/...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-sm w-100" style="background:#1a6b5e;color:#fff;border-radius:8px;">
                            Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Canlı Önizleme --}}
    @if($connected && $setting->spotify_enabled)
    <div class="col-12">
        <div class="setting-card">
            <h6>Canlı Önizleme</h6>
            <div id="preview-wrap">
                <div class="text-center text-muted py-3" style="font-size:.82rem;">Yükleniyor...</div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function manualRefresh() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Yenileniyor...';
    fetch('{{ route("admin.spotify.refresh") }}', {
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
    })
    .then(r=>r.json())
    .then(d=>{
        document.getElementById('refresh-result').innerHTML =
            d.ok ? '<span class="text-success">✓ Token yenilendi</span>'
                 : '<span class="text-danger">✗ Yenileme başarısız</span>';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Token\'ı Manuel Yenile';
    });
}

// Canlı önizleme
const preview = document.getElementById('preview-wrap');
if (preview) {
    fetch('/spotify/status')
    .then(r=>r.json())
    .then(d=>{
        if (d.now_playing) {
            const np = d.now_playing;
            const pct = np.duration_ms > 0 ? Math.round(np.progress_ms / np.duration_ms * 100) : 0;
            preview.innerHTML = `
                <div style="display:flex;align-items:center;gap:14px;">
                    ${np.cover ? `<img src="${np.cover}" style="width:56px;height:56px;border-radius:8px;object-fit:cover;">` : ''}
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:.88rem;">${np.name}</div>
                        <div style="font-size:.75rem;color:#888;">${np.artists}</div>
                        <div style="background:#f0f0f0;border-radius:4px;height:4px;margin-top:8px;">
                            <div style="background:#1DB954;width:${pct}%;height:4px;border-radius:4px;"></div>
                        </div>
                    </div>
                    <span style="font-size:.72rem;color:#1DB954;font-weight:700;">▶ Çalıyor</span>
                </div>`;
        } else {
            preview.innerHTML = '<div class="text-muted" style="font-size:.82rem;">Şu an müzik çalmıyor.</div>';
        }
    });
}
</script>
@endpush
