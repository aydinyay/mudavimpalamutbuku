@extends('layouts.admin')

@section('title', 'Sunucu Araçları')
@section('page_title', 'Sunucu Araçları')

@section('content')
<div class="row g-4">

    {{-- Cache Temizle --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#fff3cd;display:flex;align-items:center;justify-content:center;font-size:22px;">🗑️</div>
                    <div>
                        <h6 class="mb-0 fw-bold">Cache Temizle</h6>
                        <small class="text-muted">Bootstrap cache ve view cache</small>
                    </div>
                </div>
                <button class="btn btn-warning w-100" onclick="runTool('cache')">Temizle</button>
                <div class="tool-output mt-2" id="output-cache"></div>
            </div>
        </div>
    </div>

    {{-- Migration --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#d1e7dd;display:flex;align-items:center;justify-content:center;font-size:22px;">🗄️</div>
                    <div>
                        <h6 class="mb-0 fw-bold">Migration Çalıştır</h6>
                        <small class="text-muted">php artisan migrate --force</small>
                    </div>
                </div>
                <button class="btn btn-success w-100" onclick="runTool('migrate')">Migrate</button>
                <div class="tool-output mt-2" id="output-migrate"></div>
            </div>
        </div>
    </div>

    {{-- Mail Testi --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#cfe2ff;display:flex;align-items:center;justify-content:center;font-size:22px;">📧</div>
                    <div>
                        <h6 class="mb-0 fw-bold">Mail Testi</h6>
                        <small class="text-muted">SMTP bağlantısını doğrula</small>
                    </div>
                </div>
                <div class="input-group mb-2">
                    <input type="email" id="mail-to" class="form-control" value="aydinyay@gmail.com" placeholder="Hedef e-posta">
                    <button class="btn btn-primary" onclick="runTool('mail')">Gönder</button>
                </div>
                <div class="tool-output" id="output-mail"></div>
            </div>
        </div>
    </div>

    {{-- Sistem Bilgisi --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#e2d9f3;display:flex;align-items:center;justify-content:center;font-size:22px;">💻</div>
                    <div>
                        <h6 class="mb-0 fw-bold">Sistem Bilgisi</h6>
                        <small class="text-muted">PHP, Laravel, disk durumu</small>
                    </div>
                </div>
                <button class="btn btn-secondary w-100" onclick="runTool('sysinfo')">Bilgileri Al</button>
                <div class="tool-output mt-2" id="output-sysinfo"></div>
            </div>
        </div>
    </div>

</div>

<style>
.tool-output {
    font-size: 12px;
    font-family: monospace;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 8px 12px;
    white-space: pre-wrap;
    display: none;
    max-height: 180px;
    overflow-y: auto;
}
.tool-output.visible { display: block; }
</style>

<script>
async function runTool(tool) {
    const map = {
        cache:   { url: '{{ route("admin.server-tools.cache") }}',   method: 'POST', output: 'output-cache' },
        migrate: { url: '{{ route("admin.server-tools.migrate") }}', method: 'POST', output: 'output-migrate' },
        mail:    { url: '{{ route("admin.server-tools.mail") }}',    method: 'POST', output: 'output-mail' },
        sysinfo: { url: '{{ route("admin.server-tools.sysinfo") }}', method: 'GET',  output: 'output-sysinfo' },
    };
    const cfg = map[tool];
    const el = document.getElementById(cfg.output);
    el.textContent = 'Çalışıyor...';
    el.classList.add('visible');

    try {
        const opts = { method: cfg.method, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } };
        if (cfg.method === 'POST') {
            const body = new FormData();
            if (tool === 'mail') body.append('email', document.getElementById('mail-to').value);
            opts.body = body;
        }
        const res = await fetch(cfg.url, opts);
        const data = await res.json();
        el.textContent = JSON.stringify(data, null, 2);
    } catch (e) {
        el.textContent = 'Hata: ' + e.message;
    }
}
</script>
@endsection
