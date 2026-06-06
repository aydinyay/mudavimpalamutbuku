@extends('layouts.admin')

@section('title', 'Site Görselleri')
@section('page_title', 'Site Görselleri')

@push('styles')
<style>
.media-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    transition: box-shadow .2s;
}
.media-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
.media-preview {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    display: block;
    background: linear-gradient(135deg, #e8f4f2, #d0e8e4);
}
.media-placeholder {
    width: 100%;
    aspect-ratio: 16/9;
    background: linear-gradient(135deg, #f0f4f8, #e2eaf2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 8px;
    color: #bbb;
}
.upload-zone {
    border: 2px dashed #ddd;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
}
.upload-zone:hover, .upload-zone.drag { border-color: #1a6b5e; background: #f0f8f6; }
.upload-zone input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-zone .preview-thumb {
    max-height: 60px; border-radius: 6px; margin-bottom: 8px; display: none;
}
</style>
@endpush

@section('content')

<div class="row g-3 mb-4">
    <div class="col-12">
        <p class="text-muted mb-0" style="font-size:.85rem;">
            <i class="bi bi-info-circle me-1"></i>
            Görseller otomatik olarak optimize edilir (WebP formatına çevrilir). Önerilen boyutlar her kart altında belirtilmiştir.
        </p>
    </div>
</div>

<div class="row g-4">
    @foreach($areas as $key => $area)
    <div class="col-md-6">
        <div class="media-card">
            {{-- Preview --}}
            @if($setting->$key)
                <img src="{{ $setting->$key . '?v=' . time() }}" alt="{{ $area['label'] }}" class="media-preview">
            @else
                <div class="media-placeholder">
                    <i class="bi {{ $area['icon'] }}" style="font-size:2.5rem;opacity:.3;"></i>
                    <span style="font-size:.78rem;">Henüz görsel yüklenmedi</span>
                </div>
            @endif

            {{-- Info & Upload --}}
            <div class="p-4">
                <div class="d-flex align-items-start justify-content-between mb-1">
                    <div>
                        <h6 class="mb-0 fw-700">{{ $area['label'] }}</h6>
                        <div style="font-size:.72rem;color:#aaa;">{{ $area['width'] }} × {{ $area['height'] }}px önerilir</div>
                    </div>
                    @if($setting->$key)
                    <span class="badge" style="background:#e6f4f1;color:#1a6b5e;font-size:.68rem;">✓ Yüklü</span>
                    @endif
                </div>
                <p style="font-size:.78rem;color:#888;margin:8px 0 14px;">{{ $area['desc'] }}</p>

                {{-- Upload form --}}
                <form method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="key" value="{{ $key }}">
                    <div class="upload-zone mb-3" id="zone-{{ $key }}">
                        <input type="file" name="image" accept="image/*"
                               onchange="previewFile(this, '{{ $key }}')">
                        <img class="preview-thumb" id="thumb-{{ $key }}" alt="">
                        <div id="zone-text-{{ $key }}">
                            <i class="bi bi-cloud-upload" style="font-size:1.4rem;color:#ccc;display:block;margin-bottom:4px;"></i>
                            <span style="font-size:.78rem;color:#aaa;">Fotoğraf seç veya sürükle</span>
                            <div style="font-size:.68rem;color:#ccc;margin-top:2px;">JPG, PNG, WEBP — maks. 10 MB</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm w-100"
                            style="background:#1a6b5e;color:#fff;border-radius:8px;">
                        <i class="bi bi-cloud-upload me-1"></i>Yükle & Kaydet
                    </button>
                </form>

                {{-- Delete --}}
                @if($setting->$key)
                <form method="POST" action="{{ route('admin.media.delete') }}" class="mt-2"
                      onsubmit="return confirm('Görseli kaldırmak istediğinize emin misiniz?')">
                    @csrf
                    <input type="hidden" name="key" value="{{ $key }}">
                    <button class="btn btn-sm btn-outline-danger w-100" style="border-radius:8px;">
                        <i class="bi bi-trash me-1"></i>Görseli Kaldır
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
function previewFile(input, key) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var thumb = document.getElementById('thumb-' + key);
        var text  = document.getElementById('zone-text-' + key);
        thumb.src = e.target.result;
        thumb.style.display = 'block';
        text.style.display  = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

// Drag & drop highlight
document.querySelectorAll('.upload-zone').forEach(function(zone) {
    zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('drag'); });
    zone.addEventListener('dragleave', function()  { zone.classList.remove('drag'); });
    zone.addEventListener('drop',     function(e) { e.preventDefault(); zone.classList.remove('drag'); });
});
</script>
@endpush
