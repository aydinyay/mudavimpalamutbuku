@extends('layouts.admin')

@section('title', 'QR Kodlar')
@section('page_title', 'QR Kodlar')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0 small">Her masaya özgü QR kod üretin. Müşteriler taratınca menüye ulaşır.</p>
    <a href="{{ route('admin.qrcodes.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i>Yazdır / PDF
    </a>
</div>

@foreach($areas as $area)
@if($area->tables->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2">
        <span class="fw-600" style="font-size:.9rem;color:#444;">
            <i class="bi bi-geo-alt me-1 text-muted"></i>{{ $area->name_tr }}
        </span>
        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem;">
            {{ $area->tables->count() }} masa
        </span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0" style="font-size:.88rem;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4" style="width:110px;">QR</th>
                    <th>Masa</th>
                    <th>Durum</th>
                    <th>Tarama</th>
                    <th class="text-end pe-4">İşlem</th>
                </tr>
            </thead>
            <tbody>
            @foreach($area->tables as $table)
            <tr class="align-middle">
                <td class="ps-4">
                    @if($table->qrCode && $table->qrCode->svg_content)
                        <div style="width:90px;height:90px;border-radius:6px;border:1px solid #e0e0e0;overflow:hidden;background:#fff;">
                            <div style="width:90px;height:90px;display:flex;align-items:center;justify-content:center;">
                                {!! preg_replace('/<svg([^>]*)>/i', '<svg$1 width="90" height="90" style="display:block;">', $table->qrCode->svg_content) !!}
                            </div>
                        </div>
                    @else
                        <div style="width:90px;height:90px;background:#f5f5f5;border-radius:6px;border:1px solid #e0e0e0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;">
                            <i class="bi bi-qr-code text-muted" style="font-size:1.4rem;"></i>
                            <span style="font-size:.6rem;color:#aaa;">Yok</span>
                        </div>
                    @endif
                </td>
                <td>
                    <span class="fw-600">{{ $table->table_number }}</span>
                </td>
                <td>
                    @if($table->qrCode && $table->qrCode->svg_content)
                        <span class="badge" style="background:#d1fae5;color:#065f46;font-weight:500;font-size:.72rem;">
                            <i class="bi bi-check-circle me-1"></i>Aktif
                        </span>
                    @else
                        <span class="badge" style="background:#f3f4f6;color:#6b7280;font-weight:500;font-size:.72rem;">
                            Üretilmedi
                        </span>
                    @endif
                </td>
                <td class="text-muted">
                    @if($table->qrCode)
                        {{ $table->qrCode->scan_count }} tarama
                    @else
                        —
                    @endif
                </td>
                <td class="text-end pe-4">
                    <form method="POST" action="{{ route('admin.qrcodes.generate', $table) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm"
                                style="background:var(--color-coral);color:#fff;border-radius:6px;font-size:.78rem;padding:4px 12px;">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            {{ $table->qrCode ? 'Yenile' : 'Üret' }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

@endsection
