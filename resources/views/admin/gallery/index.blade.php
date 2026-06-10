@extends('layouts.admin')

@section('title', 'Galeri')
@section('page_title', 'Fotoğraf Galerisi')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Galeri sayfasında görünecek restoran fotoğrafları.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.gallery.bulk') }}" class="btn btn-sm"
           style="background:linear-gradient(135deg,#1a7fa8,#2ea887);color:#fff;border-radius:8px;">
            <i class="bi bi-cloud-upload me-1"></i> Toplu Yükle
        </a>
        <a href="{{ route('admin.gallery.create') }}" class="btn btn-sm btn-outline-secondary"
           style="border-radius:8px;">
            <i class="bi bi-plus-lg me-1"></i> Tekli Ekle
        </a>
    </div>
</div>

@if($photos->isNotEmpty())
<div class="row g-3">
    @foreach($photos as $photo)
    <div class="col-6 col-sm-4 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:10px;overflow:hidden;">
            <div style="aspect-ratio:1;overflow:hidden;background:#f0f0f0;">
                <img src="{{ asset('gallery/' . $photo->filename) }}"
                     alt="{{ $photo->alt_tr ?? $photo->alt ?? '' }}"
                     loading="lazy"
                     style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="card-body p-2">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <small class="text-muted text-truncate" style="max-width:70%;">
                        {{ $photo->alt_tr ?: '—' }}
                    </small>
                    <span class="badge bg-secondary" style="font-size:.65rem;">
                        #{{ $photo->sort_order }}
                    </span>
                </div>
                <div class="d-flex gap-1">
                    @if($photo->active)
                        <span class="badge" style="background:#2ea887;font-size:.65rem;">Aktif</span>
                    @else
                        <span class="badge bg-secondary" style="font-size:.65rem;">Pasif</span>
                    @endif
                </div>
            </div>
            <div class="card-footer p-2 d-flex gap-1" style="background:#f8f9fa;">
                <a href="{{ route('admin.gallery.edit', $photo) }}"
                   class="btn btn-sm btn-outline-secondary flex-fill" style="font-size:.75rem;">
                    <i class="bi bi-pencil"></i> Düzenle
                </a>
                <form action="{{ route('admin.gallery.destroy', $photo) }}"
                      method="POST"
                      onsubmit="return confirm('Bu fotoğrafı silmek istediğinizden emin misiniz?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:.75rem;">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-images fs-1 d-block mb-3" style="opacity:.3;"></i>
        <p class="mb-3">Henüz fotoğraf yüklenmemiş.</p>
        <a href="{{ route('admin.gallery.create') }}" class="btn btn-sm"
           style="background:#1a7fa8;color:#fff;border-radius:8px;">
            <i class="bi bi-plus-lg me-1"></i> İlk Fotoğrafı Ekle
        </a>
    </div>
</div>
@endif

@endsection
