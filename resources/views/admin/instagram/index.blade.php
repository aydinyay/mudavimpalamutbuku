@extends('layouts.admin')

@section('title', 'Instagram Gönderileri')
@section('page_title', 'Instagram Canlı Galeri')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted small mb-0">Instagram'dan çekilen gönderiler. Göz ikonuyla gizleyebilirsiniz.</p>
    <div class="d-flex gap-2">
        @if(!$hasToken)
        <span class="badge bg-warning text-dark py-2 px-3">
            <i class="bi bi-exclamation-triangle me-1"></i>Instagram token girilmemiş
        </span>
        @else
        <form method="POST" action="{{ route('admin.instagram.token-refresh') }}">
            @csrf
            <button class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-key me-1"></i>Token Yenile
            </button>
        </form>
        <form method="POST" action="{{ route('admin.instagram.sync') }}">
            @csrf
            <button class="btn btn-sm" style="background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045);color:#fff;border-radius:6px;">
                <i class="bi bi-arrow-clockwise me-1"></i>Sync
            </button>
        </form>
        @endif
    </div>
</div>

@if(!$hasToken)
<div class="alert alert-warning" style="border-radius:12px;">
    <h6 class="fw-700"><i class="bi bi-instagram me-2"></i>Instagram Bağlantısı Kurulmamış</h6>
    <p class="mb-2 small">Canlı galeri için aşağıdaki bilgileri <code>.env</code> dosyasına ekleyin:</p>
    <pre class="mb-0 small bg-white p-2 rounded">INSTAGRAM_ACCESS_TOKEN=IGQVJx...
INSTAGRAM_USER_ID=123456789</pre>
    <p class="mt-2 mb-0 small text-muted">
        <a href="https://developers.facebook.com/apps/" target="_blank">developers.facebook.com</a> →
        App oluştur → Instagram Graph API → Long-lived token al
    </p>
</div>
@endif

@if($posts->isNotEmpty())
<div class="row g-3">
    @foreach($posts as $post)
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px;overflow:hidden;{{ !$post->is_visible ? 'opacity:.5' : '' }}">
            <div style="position:relative;aspect-ratio:1;background:#f0f0f0;">
                <img src="{{ $post->media_url }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy"
                     alt="{{ $post->caption ? mb_substr($post->caption,0,40) : '' }}">
                @if(!$post->is_visible)
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-eye-slash text-white fs-3"></i>
                </div>
                @endif
            </div>
            <div class="card-body p-2">
                @if($post->caption)
                <p class="small text-muted mb-1" style="font-size:.72rem;line-height:1.4;">
                    {{ mb_substr($post->caption,0,80) }}{{ mb_strlen($post->caption)>80?'…':'' }}
                </p>
                @endif
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="text-muted" style="font-size:.68rem;">{{ $post->posted_at->format('d.m.Y') }}</span>
                    <form method="POST" action="{{ route('admin.instagram.toggle', $post) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $post->is_visible ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                style="padding:2px 8px;font-size:.7rem;">
                            <i class="bi bi-{{ $post->is_visible ? 'eye-slash' : 'eye' }}"></i>
                            {{ $post->is_visible ? 'Gizle' : 'Yayına Al' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $posts->links() }}</div>
@else
<div class="text-center py-5 text-muted">
    <i class="bi bi-instagram fs-1 d-block mb-3 opacity-25"></i>
    <p>Henüz gönderi yok. Token ekleyip Sync butonuna basın.</p>
</div>
@endif

@if($lastSync)
<p class="text-muted small mt-3 text-end">
    Son sync: {{ $lastSync->synced_at->diffForHumans() }} — {{ $lastSync->status === 'ok' ? '✓' : '✗ '.$lastSync->error }}
</p>
@endif

@endsection
