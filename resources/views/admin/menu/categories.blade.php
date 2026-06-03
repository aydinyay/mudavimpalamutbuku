@extends('layouts.admin')

@section('title', 'Kategoriler')
@section('page_title', 'Menü Kategorileri')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.menu.categories.create') }}" class="btn" style="background:var(--color-sea);color:#fff;border-radius:8px;">
        <i class="bi bi-plus me-1"></i>Yeni Kategori
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-mudavim mb-0">
            <thead><tr><th>Sıra</th><th>Emoji</th><th>TR</th><th>EN</th><th>DE</th><th>Ürün</th><th>Aktif</th><th></th></tr></thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->sort_order }}</td>
                    <td>{{ $cat->icon_emoji }}</td>
                    <td>{{ $cat->name_tr }}</td>
                    <td class="text-muted">{{ $cat->name_en }}</td>
                    <td class="text-muted">{{ $cat->name_de }}</td>
                    <td>{{ $cat->items_count }}</td>
                    <td>@if($cat->is_active)<span class="badge bg-success">Evet</span>@else<span class="badge bg-secondary">Hayır</span>@endif</td>
                    <td>
                        <a href="{{ route('admin.menu.categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary py-0">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.menu.categories.destroy', $cat) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0"
                                    data-confirm-delete="Bu kategoriyi silmek istiyor musunuz?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
