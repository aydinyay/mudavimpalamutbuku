@extends('layouts.admin')

@section('title', isset($photo) ? 'Fotoğraf Düzenle' : 'Fotoğraf Ekle')
@section('page_title', isset($photo) ? 'Fotoğraf Düzenle' : 'Yeni Fotoğraf Ekle')

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST"
      action="{{ isset($photo) ? route('admin.gallery.update', $photo) : route('admin.gallery.store') }}"
      enctype="multipart/form-data">
    @csrf
    @isset($photo) @method('PUT') @endisset

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-3" style="background:linear-gradient(135deg,#1a7fa8,#2ea887);border-radius:12px 12px 0 0;">
            <h6 class="mb-0 text-white fw-600">
                <i class="bi bi-image me-2"></i>
                {{ isset($photo) ? 'Fotoğrafı Düzenle' : 'Yeni Fotoğraf Yükle' }}
            </h6>
        </div>
        <div class="card-body p-4">

            {{-- Mevcut fotoğraf (edit modunda) --}}
            @isset($photo)
            <div class="mb-4">
                <label class="form-label fw-500">Mevcut Fotoğraf</label>
                <div>
                    <img src="{{ asset('gallery/' . $photo->filename) }}"
                         alt="{{ $photo->alt_tr ?? '' }}"
                         style="max-width:220px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.12);">
                </div>
            </div>
            @endisset

            {{-- Fotoğraf yükleme --}}
            <div class="mb-4">
                <label class="form-label fw-500">
                    Fotoğraf
                    @isset($photo)
                        <span class="text-muted fw-normal">(değiştirmek için yükleyin)</span>
                    @else
                        <span class="text-danger">*</span>
                    @endisset
                </label>
                <input type="file"
                       name="photo"
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       class="form-control @error('photo') is-invalid @enderror"
                       {{ isset($photo) ? '' : 'required' }}>
                <div class="form-text">JPEG, PNG veya WebP — maks. 5 MB. Otomatik olarak 1200×900 px'e dönüştürülür.</div>
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Alt metinler (dil sekmeleri) --}}
            <ul class="nav nav-tabs mb-3" id="langTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-tr" data-bs-toggle="tab" data-bs-target="#pane-tr"
                            type="button" role="tab">🇹🇷 TR</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-en" data-bs-toggle="tab" data-bs-target="#pane-en"
                            type="button" role="tab">🇬🇧 EN</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-de" data-bs-toggle="tab" data-bs-target="#pane-de"
                            type="button" role="tab">🇩🇪 DE</button>
                </li>
            </ul>

            <div class="tab-content mb-4" id="langTabsContent">
                <div class="tab-pane fade show active" id="pane-tr" role="tabpanel">
                    <label class="form-label fw-500">Alt Metin (TR)</label>
                    <input type="text" name="alt_tr"
                           class="form-control @error('alt_tr') is-invalid @enderror"
                           maxlength="200"
                           value="{{ old('alt_tr', $photo->alt_tr ?? '') }}"
                           placeholder="Deniz manzarası, masa düzenlemesi...">
                    @error('alt_tr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="tab-pane fade" id="pane-en" role="tabpanel">
                    <label class="form-label fw-500">Alt Text (EN)</label>
                    <input type="text" name="alt_en"
                           class="form-control @error('alt_en') is-invalid @enderror"
                           maxlength="200"
                           value="{{ old('alt_en', $photo->alt_en ?? '') }}"
                           placeholder="Sea view, table setting...">
                    @error('alt_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="tab-pane fade" id="pane-de" role="tabpanel">
                    <label class="form-label fw-500">Bildtext (DE)</label>
                    <input type="text" name="alt_de"
                           class="form-control @error('alt_de') is-invalid @enderror"
                           maxlength="200"
                           value="{{ old('alt_de', $photo->alt_de ?? '') }}"
                           placeholder="Meerblick, Tischdekoration...">
                    @error('alt_de')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Sıra + Aktif --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-500">Sıra</label>
                    <input type="number" name="sort_order"
                           class="form-control @error('sort_order') is-invalid @enderror"
                           min="0" value="{{ old('sort_order', $photo->sort_order ?? 0) }}">
                    <div class="form-text">Küçük sayı önce görünür.</div>
                    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 d-flex align-items-end pb-1">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" id="activeSwitch" value="1"
                               {{ old('active', $photo->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-500" for="activeSwitch">Aktif</label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn px-4"
                style="background:linear-gradient(135deg,#1a7fa8,#2ea887);color:#fff;border-radius:8px;">
            <i class="bi bi-check-lg me-1"></i> Kaydet
        </button>
        <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline-secondary px-4"
           style="border-radius:8px;">
            <i class="bi bi-x-lg me-1"></i> İptal
        </a>
    </div>

</form>

@endsection
