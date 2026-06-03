@extends('layouts.admin')

@section('title', 'Menü Ürünleri')
@section('page_title', 'Menü Ürünleri')

@section('content')
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
    <form method="GET" class="d-flex gap-2">
        <select name="kategori" class="form-select" onchange="this.form.submit()">
            <option value="">Tüm Kategoriler</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>{{ $cat->name_tr }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('admin.menu.items.create') }}" class="btn ms-auto" style="background:var(--color-sea);color:#fff;border-radius:8px;">
        <i class="bi bi-plus me-1"></i>Yeni Ürün
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-mudavim mb-0">
            <thead>
                <tr><th>Fotoğraf</th><th>Ürün (TR)</th><th>Kategori</th><th>Fiyat</th><th>Durum</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        @if($item->image_path)
                            <img src="{{ $item->image_path }}" width="50" height="50"
                                 style="object-fit:cover;border-radius:8px;">
                        @else
                            <div style="width:50px;height:50px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-600">{{ $item->name('tr') }}</div>
                        <small class="text-muted">{{ Str::limit($item->description('tr'), 60) }}</small>
                        <div class="mt-1">
                            @if($item->is_featured)<span class="badge badge-featured">İmza</span>@endif
                            @if($item->is_seasonal)<span class="badge badge-seasonal">Sezonluk</span>@endif
                            @if($item->is_vegetarian)<span class="badge badge-vegetarian">V</span>@endif
                        </div>
                    </td>
                    <td>{{ $item->category->name_tr }}</td>
                    <td>{{ number_format($item->price, 0, ',', '.') }} ₺</td>
                    <td>
                        <button class="btn btn-sm {{ $item->is_available ? 'btn-success' : 'btn-secondary' }}"
                                onclick="toggleAvailable({{ $item->id }}, this)"
                                style="border-radius:12px;font-size:0.75rem;">
                            {{ $item->is_available ? 'Mevcut' : 'Tükendi' }}
                        </button>
                    </td>
                    <td>
                        <a href="{{ route('admin.menu.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary py-0">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.menu.items.destroy', $item) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0"
                                    data-confirm-delete="Bu ürünü silmek istiyor musunuz?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">{{ $items->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function toggleAvailable(id, btn) {
    const res  = await fetch(`/yonetim/menu/urunler/${id}/musait`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    });
    const data = await res.json();
    btn.textContent = data.is_available ? 'Mevcut' : 'Tükendi';
    btn.className   = `btn btn-sm ${data.is_available ? 'btn-success' : 'btn-secondary'}`;
    btn.style.borderRadius = '12px';
    btn.style.fontSize = '0.75rem';
}
</script>
@endpush
