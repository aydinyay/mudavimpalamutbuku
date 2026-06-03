@extends('layouts.admin')

@section('title', 'Masa Planı')
@section('page_title', 'Masa Planı')

@section('content')

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.tables.create') }}" class="btn" style="background:var(--color-sea);color:#fff;border-radius:8px;">
        <i class="bi bi-plus me-1"></i>Yeni Masa Ekle
    </a>
</div>

@foreach($areas as $area)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-700">
            @if($area->type === 'beach') 🌊
            @elseif($area->type === 'terrace') 🌿
            @elseif($area->type === 'indoor') 🏠
            @else ⚓ @endif
            {{ $area->name_tr }}
        </h6>
        <span class="text-muted small">{{ $area->tables->count() }} masa</span>
    </div>
    <div class="card-body">
        {{-- Visual plan --}}
        <div class="position-relative border rounded mb-3"
             style="width:100%;height:200px;overflow:hidden;background:#f0f4f0;"
             data-area-id="{{ $area->id }}">
            @foreach($area->tables as $table)
            <div class="position-absolute d-flex align-items-center justify-content-center fw-700"
                 style="left:{{ $table->pos_x }}px;top:{{ $table->pos_y }}px;
                        width:{{ $table->width_px }}px;height:{{ $table->height_px }}px;
                        background:var(--color-sea);color:#fff;
                        border-radius:{{ $table->shape === 'round' ? '50%' : '8px' }};
                        font-size:0.8rem;cursor:pointer;"
                 title="{{ $table->table_number }} — maks {{ $table->seats_max }} kişi">
                {{ $table->table_number }}
            </div>
            @endforeach
        </div>

        {{-- Table list --}}
        <div class="table-responsive">
            <table class="table table-mudavim table-sm mb-0">
                <thead><tr><th>No</th><th>Kod</th><th>Min</th><th>Maks</th><th>Şekil</th><th>Aktif</th><th></th></tr></thead>
                <tbody>
                    @foreach($area->tables as $t)
                    <tr>
                        <td>{{ $t->table_number }}</td>
                        <td><code>{{ $t->table_code }}</code></td>
                        <td>{{ $t->seats_min }}</td>
                        <td>{{ $t->seats_max }}</td>
                        <td>{{ $t->shape }}</td>
                        <td>
                            @if($t->is_active)<span class="badge bg-success">Evet</span>
                            @else<span class="badge bg-secondary">Hayır</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('admin.tables.edit', $t) }}" class="btn btn-sm btn-outline-secondary py-0">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.tables.destroy', $t) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger py-0"
                                        data-confirm-delete="'{{ $t->table_number }}' masasını silmek istiyor musunuz?">
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
</div>
@endforeach

@endsection
