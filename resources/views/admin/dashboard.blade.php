@extends('layouts.admin')

@section('title', 'Yönetim Paneli')
@section('page_title', 'Özet')

@section('content')

{{-- Today's stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['today_count'] }}</div>
            <div class="stat-label">Bugünkü Rezervasyon</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card" style="border-color:var(--color-coral);">
            <div class="stat-value">{{ $stats['pending_count'] }}</div>
            <div class="stat-label">Onay Bekleyen</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card" style="border-color:#4caf50;">
            <div class="stat-value">{{ $stats['total_guests'] }}</div>
            <div class="stat-label">Bugünkü Misafir</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card" style="border-color:#ff9800;">
            <div class="stat-value">{{ $stats['tables_active'] }}</div>
            <div class="stat-label">Aktif Masa</div>
        </div>
    </div>
</div>

{{-- Today's reservations --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-700">Bugünkü Rezervasyonlar</h6>
        <a href="{{ route('admin.reservations.create') }}" class="btn btn-sm" style="background:var(--color-sea);color:#fff;border-radius:8px;">
            <i class="bi bi-plus me-1"></i>Yeni Rezervasyon
        </a>
    </div>
    <div class="card-body p-0">
        @if($todayReservations->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                Bugün için rezervasyon yok.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-mudavim mb-0">
                    <thead>
                        <tr>
                            <th>Saat</th>
                            <th>Misafir</th>
                            <th>Kişi</th>
                            <th>Masa</th>
                            <th>Durum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todayReservations as $r)
                        <tr>
                            <td><strong>{{ $r->arrival_time }}</strong></td>
                            <td>
                                {{ $r->guest_name }}
                                @if($r->source === 'boat')
                                    <span class="badge bg-info ms-1" title="Tekne ile geliyor"><i class="bi bi-water"></i></span>
                                @endif
                                <br><small class="text-muted">{{ $r->guest_phone }}</small>
                            </td>
                            <td>{{ $r->guest_count }}</td>
                            <td>
                                @if($r->table)
                                    {{ $r->table->table_number }}
                                    <small class="text-muted">({{ $r->table->area->name('tr') }})</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $r->statusBadgeClass() }}">
                                    {{ __('reservation.status_' . $r->status) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.reservations.status', $r) }}" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block;"
                                            onchange="this.form.submit()">
                                        @foreach(['pending','confirmed','no_show','completed','cancelled'] as $s)
                                            <option value="{{ $s }}" @selected($r->status === $s)>
                                                {{ __('reservation.status_' . $s) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
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
