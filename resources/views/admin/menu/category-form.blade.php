@extends('layouts.admin')

@section('title', isset($category) ? 'Kategori Düzenle' : 'Yeni Kategori')
@section('page_title', isset($category) ? 'Kategori Düzenle' : 'Yeni Kategori')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <form method="POST"
              action="{{ isset($category) ? route('admin.menu.categories.update', $category) : route('admin.menu.categories.store') }}">
            @csrf
            @if(isset($category)) @method('PUT') @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label class="form-label">TR İsim</label>
                            <input type="text" name="name_tr" class="form-control"
                                   value="{{ old('name_tr', $category->name_tr ?? '') }}" required>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Emoji</label>
                            <input type="text" name="icon_emoji" class="form-control"
                                   value="{{ old('icon_emoji', $category->icon_emoji ?? '') }}" maxlength="10">
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Sıra</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">EN İsim</label>
                            <input type="text" name="name_en" class="form-control"
                                   value="{{ old('name_en', $category->name_en ?? '') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">DE İsim</label>
                            <input type="text" name="name_de" class="form-control"
                                   value="{{ old('name_de', $category->name_de ?? '') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       @checked(old('is_active', $category->is_active ?? true))>
                                <label class="form-check-label">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn" style="background:var(--color-sea);color:#fff;">Kaydet</button>
                    <a href="{{ route('admin.menu.categories.index') }}" class="btn btn-outline-secondary">İptal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
