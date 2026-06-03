@extends('layouts.admin')

@section('title', 'Rezervasyonlar')
@section('page_title', 'Rezervasyonlar')

@section('content')

<div class="d-flex gap-2 mb-4 flex-wrap align-items-center">
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="tarih" class="form-control" value="{{ $date }}" style="width:auto;">
        <button class="btn btn-outline-secondary">Göster</button>
    </form>
    <a href="{{ route('admin.reservations.create') }}" class="btn ms-auto" style="background:var(--color-sea);color:#fff;border-radius:8px;">
        <i class="bi bi-plus me-1"></i>Yeni Rezervasyon
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($reservations->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                {{ $date }} için rezervasyon yok.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-mudavim mb-0">
                <thead>
                    <tr>
                        <th>Saat</th><th>Misafir</th><th>Kişi</th><th>Masa</th>
                        <th>Kaynak</th><th>Durum</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $r)
                    <tr>
                        <td><strong>{{ $r->arrival_time }}</strong></td>
                        <td>
                            {{ $r->guest_name }}<br>
                            <small class="text-muted">{{ $r->guest_phone }}</small>
                        </td>
                        <td>{{ $r->guest_count }}</td>
                        <td>{{ $r->table?->table_number ?? '—' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $r->source }}</span>
                            @if($r->source === 'boat')<i class="bi bi-water text-info ms-1"></i>@endif
                        </td>
                        <td><span class="status-badge {{ $r->statusBadgeClass() }}">{{ __('reservation.status_' . $r->status) }}</span></td>
                        <td>
                            <a href="{{ route('admin.reservations.edit', $r) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
