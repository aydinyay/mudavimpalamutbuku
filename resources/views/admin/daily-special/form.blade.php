@extends('layouts.admin')

@section('title', isset($special) ? 'Günlük Özel Düzenle' : 'Günlük Özel Ekle')
@section('page_title', isset($special) ? 'Günlük Özel Düzenle' : 'Günlük Özel Ekle')

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
      action="{{ isset($special) ? route('admin.daily-specials.update', $special) : route('admin.daily-specials.store') }}"
      enctype="multipart/form-data">
    @csrf
    @isset($special) @method('PUT') @endisset

    {{-- Ana Kart --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-3" style="background:linear-gradient(135deg,#1a7fa8,#2ea887);border-radius:12px 12px 0 0;">
            <h6 class="mb-0 text-white fw-600">
                <i class="bi bi-calendar-check me-2"></i>
                {{ isset($special) ? 'Kaydı Düzenle' : 'Yeni Günlük Özel' }}
            </h6>
        </div>
        <div class="card-body p-4">

            {{-- Tarih + Fiyat + Aktif --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-500">Tarih <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                           value="{{ old('date', isset($special) ? $special->date->format('Y-m-d') : today()->format('Y-m-d')) }}"
                           required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-500">Fiyat (₺)</label>
                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                           step="0.01" min="0"
                           value="{{ old('price', isset($special) ? $special->price : '') }}"
                           placeholder="Boş bırakılabilir">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                               {{ old('is_active', isset($special) ? $special->is_active : true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-500" for="isActive">Aktif</label>
                    </div>
                </div>
            </div>

            {{-- Görsel --}}
            <div class="mb-4">
                <label class="form-label fw-500">Yemek Görseli</label>

                @isset($special)
                    @if($special->image_path)
                    <div class="mb-2 d-flex align-items-center gap-3">
                        <img src="{{ asset('daily-specials/' . $special->image_path) }}"
                             alt="{{ $special->title_tr }}"
                             style="height:90px;border-radius:8px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,.12);">
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage" value="1">
                                <label class="form-check-label text-danger small" for="removeImage">Görseli kaldır</label>
                            </div>
                        </div>
                    </div>
                    @endif
                @endisset

                <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/webp"
                       class="form-control @error('image') is-invalid @enderror">
                <div class="form-text">JPEG, PNG veya WebP — maks. 5 MB. Otomatik 800×600 WebP'ye dönüştürülür.</div>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Dil Sekmeleri --}}
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

            <div class="tab-content" id="langTabsContent">

                {{-- TR --}}
                <div class="tab-pane fade show active" id="pane-tr" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label fw-500">Başlık (TR) <span class="text-danger">*</span></label>
                        <input type="text" name="title_tr" class="form-control @error('title_tr') is-invalid @enderror"
                               maxlength="200"
                               value="{{ old('title_tr', $special->title_tr ?? '') }}"
                               placeholder="Ör: Izgara Ahtapot">
                        @error('title_tr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Açıklama (TR)</label>
                        <textarea name="description_tr" class="form-control @error('description_tr') is-invalid @enderror"
                                  rows="3" placeholder="Kısa açıklama...">{{ old('description_tr', $special->description_tr ?? '') }}</textarea>
                        @error('description_tr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- EN --}}
                <div class="tab-pane fade" id="pane-en" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label fw-500">Title (EN)</label>
                        <input type="text" name="title_en" class="form-control @error('title_en') is-invalid @enderror"
                               maxlength="200"
                               value="{{ old('title_en', $special->title_en ?? '') }}"
                               placeholder="e.g. Grilled Octopus">
                        @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Description (EN)</label>
                        <textarea name="description_en" class="form-control @error('description_en') is-invalid @enderror"
                                  rows="3" placeholder="Short description...">{{ old('description_en', $special->description_en ?? '') }}</textarea>
                        @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- DE --}}
                <div class="tab-pane fade" id="pane-de" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label fw-500">Titel (DE)</label>
                        <input type="text" name="title_de" class="form-control @error('title_de') is-invalid @enderror"
                               maxlength="200"
                               value="{{ old('title_de', $special->title_de ?? '') }}"
                               placeholder="z.B. Gegrillter Tintenfisch">
                        @error('title_de')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Beschreibung (DE)</label>
                        <textarea name="description_de" class="form-control @error('description_de') is-invalid @enderror"
                                  rows="3" placeholder="Kurze Beschreibung...">{{ old('description_de', $special->description_de ?? '') }}</textarea>
                        @error('description_de')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

            </div>{{-- /tab-content --}}
        </div>
    </div>

    {{-- Butonlar --}}
    <div class="d-flex gap-2">
        <button type="submit" class="btn px-4"
                style="background:linear-gradient(135deg,#1a7fa8,#2ea887);color:#fff;border-radius:8px;">
            <i class="bi bi-check-lg me-1"></i> Kaydet
        </button>
        <a href="{{ route('admin.daily-specials.index') }}" class="btn btn-outline-secondary px-4"
           style="border-radius:8px;">
            <i class="bi bi-x-lg me-1"></i> İptal
        </a>
    </div>

</form>

@endsection
