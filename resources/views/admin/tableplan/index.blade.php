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
        {{-- Visual plan (drag-drop) --}}
        <div class="position-relative border rounded mb-3"
             style="width:100%;height:240px;overflow:hidden;background:linear-gradient(135deg,#e8f4f0,#d0e8e0);"
             data-area-id="{{ $area->id }}">
            @foreach($area->tables as $table)
            <div class="table-plan-draggable position-absolute d-flex align-items-center justify-content-center fw-700"
                 data-table-id="{{ $table->id }}"
                 data-x="{{ $table->pos_x }}"
                 data-y="{{ $table->pos_y }}"
                 style="width:{{ $table->width_px ?? 56 }}px;height:{{ $table->height_px ?? 56 }}px;
                        background:var(--color-sea);color:#fff;
                        border-radius:{{ $table->shape === 'round' ? '50%' : '8px' }};
                        font-size:0.8rem;cursor:grab;user-select:none;touch-action:none;"
                 title="{{ $table->table_number }} — maks {{ $table->seats_max }} kişi">
                {{ $table->table_number }}
            </div>
            @endforeach
            <small class="position-absolute text-muted" style="bottom:4px;right:8px;font-size:0.65rem;">Masaları sürükleyerek yerleştir</small>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Masaları kayıtlı konumlarına yerleştir
    document.querySelectorAll('.table-plan-draggable').forEach(function (el) {
        var x = parseInt(el.dataset.x) || 0;
        var y = parseInt(el.dataset.y) || 0;
        el.style.left = x + 'px';
        el.style.top  = y + 'px';
    });

    var dragging  = null;
    var startX, startY, startLeft, startTop;

    document.querySelectorAll('.table-plan-draggable').forEach(function (el) {

        el.addEventListener('pointerdown', function (e) {
            e.preventDefault();
            dragging = el;
            el.setPointerCapture(e.pointerId);
            startX    = e.clientX;
            startY    = e.clientY;
            startLeft = parseInt(el.style.left) || 0;
            startTop  = parseInt(el.style.top)  || 0;
            el.style.cursor = 'grabbing';
            el.style.zIndex = '50';
        });

        el.addEventListener('pointermove', function (e) {
            if (dragging !== el) return;
            var container = el.closest('[data-area-id]');
            var rect      = container.getBoundingClientRect();
            var newLeft   = startLeft + (e.clientX - startX);
            var newTop    = startTop  + (e.clientY - startY);
            newLeft = Math.max(0, Math.min(newLeft, rect.width  - el.offsetWidth));
            newTop  = Math.max(0, Math.min(newTop,  rect.height - el.offsetHeight));
            el.style.left = newLeft + 'px';
            el.style.top  = newTop  + 'px';
        });

        el.addEventListener('pointerup', function (e) {
            if (dragging !== el) return;
            dragging = null;
            el.style.cursor = 'grab';
            el.style.zIndex = '';

            var tableId = el.dataset.tableId;
            var posX    = parseInt(el.style.left) || 0;
            var posY    = parseInt(el.style.top)  || 0;

            fetch('/yonetim/masalar/' + tableId + '/pozisyon', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ pos_x: posX, pos_y: posY })
            }).then(function (r) { return r.json(); })
              .then(function (d) {
                  if (!d.ok) { el.style.outline = '2px solid red'; }
              })
              .catch(function () { el.style.outline = '2px solid red'; });
        });
    });
});
</script>
@endpush

@endsection
