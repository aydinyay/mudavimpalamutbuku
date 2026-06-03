@extends('layouts.admin')

@section('title', isset($reservation) ? 'Rezervasyon Düzenle' : 'Yeni Rezervasyon')
@section('page_title', isset($reservation) ? 'Rezervasyon Düzenle' : 'Yeni Rezervasyon')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <form method="POST"
              action="{{ isset($reservation) ? route('admin.reservations.update', $reservation) : route('admin.reservations.store') }}">
            @csrf
            @if(isset($reservation)) @method('PUT') @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" name="guest_name" class="form-control"
                                   value="{{ old('guest_name', $reservation->guest_name ?? '') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="guest_phone" class="form-control"
                                   value="{{ old('guest_phone', $reservation->guest_phone ?? '') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="guest_email" class="form-control"
                                   value="{{ old('guest_email', $reservation->guest_email ?? '') }}">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Kişi Sayısı</label>
                            <input type="number" name="guest_count" class="form-control" min="1" max="20"
                                   value="{{ old('guest_count', $reservation->guest_count ?? 2) }}" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Kaynak</label>
                            <select name="source" class="form-select" required>
                                @foreach(['website','admin','phone','walkin','boat'] as $s)
                                    <option value="{{ $s }}" @selected(old('source', $reservation->source ?? 'admin') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Tarih</label>
                            <input type="date" name="reservation_date" class="form-control"
                                   value="{{ old('reservation_date', isset($reservation) ? $reservation->reservation_date->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Saat</label>
                            <input type="time" name="arrival_time" class="form-control"
                                   value="{{ old('arrival_time', $reservation->arrival_time ?? '') }}" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Masa</label>
                            <select name="table_id" class="form-select">
                                <option value="">— Oto —</option>
                                @foreach($tables as $t)
                                    <option value="{{ $t->id }}"
                                            @selected(old('table_id', $reservation->table_id ?? null) == $t->id)>
                                        {{ $t->table_number }} ({{ $t->area->name('tr') }}, maks {{ $t->seats_max }} kişi)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Özel İstek</label>
                            <textarea name="special_requests" class="form-control" rows="2">{{ old('special_requests', $reservation->special_requests ?? '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">İç Not (misafire görünmez)</label>
                            <textarea name="internal_notes" class="form-control" rows="2">{{ old('internal_notes', $reservation->internal_notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn" style="background:var(--color-sea);color:#fff;">Kaydet</button>
                    <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">İptal</a>
                    @if(isset($reservation))
                    <form method="POST" action="{{ route('admin.reservations.destroy', $reservation) }}" class="ms-auto">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                data-confirm-delete="Bu rezervasyonu iptal etmek istiyor musunuz?">
                            Sil
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
