@extends('layouts.admin')

@section('title', isset($item) ? 'Ürün Düzenle' : 'Yeni Ürün')
@section('page_title', isset($item) ? 'Ürün Düzenle' : 'Yeni Ürün')

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ isset($item) ? route('admin.menu.items.update', $item) : route('admin.menu.items.store') }}">
    @csrf
    @if(isset($item)) @method('PUT') @endif

    <div class="row g-4">
        {{-- Left: Details --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><strong>Ürün Detayları</strong></div>
                <div class="card-body p-4">
                    {{-- Language tabs --}}
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-tr">🇹🇷 Türkçe</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-en">🇬🇧 English</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-de">🇩🇪 Deutsch</button></li>
                    </ul>
                    <div class="tab-content">
                        @foreach(['tr'=>'Türkçe','en'=>'English','de'=>'Deutsch'] as $loc => $label)
                        <div class="tab-pane fade {{ $loc === 'tr' ? 'show active' : '' }}" id="tab-{{ $loc }}">
                            <div class="mb-3">
                                <label class="form-label">İsim ({{ strtoupper($loc) }})</label>
                                <input type="text" name="name_{{ $loc }}" class="form-control"
                                       value="{{ old('name_' . $loc, isset($item) ? $item->translation($loc)?->name : '') }}"
                                       {{ $loc === 'tr' ? 'required' : '' }}>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Açıklama ({{ strtoupper($loc) }})</label>
                                <textarea name="description_{{ $loc }}" class="form-control" rows="3">{{ old('description_' . $loc, isset($item) ? $item->translation($loc)?->description : '') }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Allergens --}}
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Alerjenler <span class="badge bg-danger" style="font-size:.65rem;">Yasal Zorunlu — Ara. 2026</span></label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($allergens as $allergen)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="allergen_ids[]" value="{{ $allergen->id }}"
                                       id="a{{ $allergen->id }}"
                                       @checked(isset($item) && $item->allergens->contains($allergen->id))>
                                <label class="form-check-label small" for="a{{ $allergen->id }}">
                                    {{ $allergen->name_tr }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- İçindekiler --}}
                    <div class="mt-4 p-3 border rounded" style="border-color:#ffc107!important;background:#fffdf0;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <strong>İçindekiler / Bileşenler</strong>
                            <span class="badge bg-warning text-dark" style="font-size:.65rem;">Yasal Zorunlu — Ara. 2026</span>
                        </div>
                        <p class="text-muted small mb-3">Üründe kullanılan tüm malzemeleri virgülle ayırarak yazın. (ör: tavuk göğsü, zeytinyağı, limon, sarımsak, tuz, karabiber)</p>
                        <div class="mb-2">
                            <label class="form-label small">🇹🇷 Türkçe</label>
                            <textarea name="ingredients_tr" class="form-control form-control-sm" rows="2">{{ old('ingredients_tr', $item->ingredients_tr ?? '') }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">🇬🇧 English</label>
                            <textarea name="ingredients_en" class="form-control form-control-sm" rows="2">{{ old('ingredients_en', $item->ingredients_en ?? '') }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">🇩🇪 Deutsch</label>
                            <textarea name="ingredients_de" class="form-control form-control-sm" rows="2">{{ old('ingredients_de', $item->ingredients_de ?? '') }}</textarea>
                        </div>
                        <div class="mt-2">
                            <label class="form-label small">Et Kökeni <span class="text-muted">(varsa: dana, kuzu, tavuk, balık vb.)</span></label>
                            <input type="text" name="meat_origin" class="form-control form-control-sm"
                                   value="{{ old('meat_origin', $item->meat_origin ?? '') }}"
                                   placeholder="ör: Ege levreği, dana bonfile">
                        </div>
                    </div>

                    {{-- Besin Değerleri --}}
                    <div class="mt-3 p-3 border rounded" style="border-color:#0dcaf0!important;background:#f0fbff;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <strong>Besin Değerleri</strong>
                            <span class="badge bg-info text-dark" style="font-size:.65rem;">Zorunlu — Ara. 2027</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 col-md-4">
                                <label class="form-label small">Porsiyon (g)</label>
                                <input type="number" name="serving_size_g" class="form-control form-control-sm"
                                       value="{{ old('serving_size_g', $item->serving_size_g ?? '') }}"
                                       min="0" max="9999" placeholder="ör: 250">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small">Kalori (kcal)</label>
                                <input type="number" name="calories" class="form-control form-control-sm"
                                       value="{{ old('calories', $item->calories ?? '') }}"
                                       min="0" max="9999" placeholder="ör: 420">
                            </div>
                            <div class="col-4 col-md-4">
                                <label class="form-label small">Protein (g)</label>
                                <input type="number" name="protein_g" class="form-control form-control-sm"
                                       value="{{ old('protein_g', $item->protein_g ?? '') }}"
                                       step="0.1" min="0" placeholder="ör: 38.5">
                            </div>
                            <div class="col-4 col-md-4">
                                <label class="form-label small">Yağ (g)</label>
                                <input type="number" name="fat_g" class="form-control form-control-sm"
                                       value="{{ old('fat_g', $item->fat_g ?? '') }}"
                                       step="0.1" min="0" placeholder="ör: 18.2">
                            </div>
                            <div class="col-4 col-md-4">
                                <label class="form-label small">Karbonhidrat (g)</label>
                                <input type="number" name="carbs_g" class="form-control form-control-sm"
                                       value="{{ old('carbs_g', $item->carbs_g ?? '') }}"
                                       step="0.1" min="0" placeholder="ör: 2.1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Pricing & Options --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><strong>Fiyat & Seçenekler</strong></div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? null) == $cat->id)>
                                    {{ $cat->name_tr }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fiyat (₺)</label>
                        <input type="number" name="price" class="form-control" step="0.01"
                               value="{{ old('price', $item->price ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fiyat (€) <small class="text-muted">opsiyonel</small></label>
                        <input type="number" name="price_eur" class="form-control" step="0.01"
                               value="{{ old('price_eur', $item->price_eur ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sıra</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', $item->sort_order ?? 0) }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fotoğraf</label>
                        @if(isset($item) && $item->image_path)
                            <img src="{{ $item->image_path }}" class="d-block mb-2 rounded" style="max-height:120px;">
                        @endif
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    @foreach([
                        'is_available'  => 'Mevcut',
                        'is_featured'   => 'İmza / Öne Çıkan',
                        'is_seasonal'   => 'Sezonluk',
                        'is_vegetarian' => 'Vejeteryan',
                        'is_vegan'      => 'Vegan',
                        'is_gluten_free'=> 'Glutensiz',
                    ] as $field => $label)
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1"
                               id="{{ $field }}"
                               @checked(old($field, $item->{$field} ?? ($field === 'is_available')))>
                        <label class="form-check-label small" for="{{ $field }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn w-100" style="background:var(--color-sea);color:#fff;border-radius:12px;padding:12px;">
                Kaydet
            </button>
            <a href="{{ route('admin.menu.items.index') }}" class="btn w-100 btn-outline-secondary mt-2">İptal</a>
        </div>
    </div>
</form>
@endsection
