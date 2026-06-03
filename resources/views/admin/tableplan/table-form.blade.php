@extends('layouts.admin')

@section('title', isset($table) ? 'Masa Düzenle' : 'Yeni Masa')
@section('page_title', isset($table) ? 'Masa Düzenle' : 'Yeni Masa')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <form method="POST"
              action="{{ isset($table) ? route('admin.tables.update', $table) : route('admin.tables.store') }}">
            @csrf
            @if(isset($table)) @method('PUT') @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Bölge</label>
                            <select name="area_id" class="form-select" required>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}"
                                            @selected(old('area_id', $table->area_id ?? null) == $area->id)>
                                        {{ $area->name_tr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Masa No</label>
                            <input type="text" name="table_number" class="form-control"
                                   value="{{ old('table_number', $table->table_number ?? '') }}"
                                   placeholder="T1" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Masa Kodu (URL)</label>
                            <input type="text" name="table_code" class="form-control"
                                   value="{{ old('table_code', $table->table_code ?? '') }}"
                                   placeholder="teras-1" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Min Kişi</label>
                            <input type="number" name="seats_min" class="form-control" min="1" max="20"
                                   value="{{ old('seats_min', $table->seats_min ?? 1) }}" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Maks Kişi</label>
                            <input type="number" name="seats_max" class="form-control" min="1" max="20"
                                   value="{{ old('seats_max', $table->seats_max ?? 4) }}" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Şekil</label>
                            <select name="shape" class="form-select">
                                @foreach(['round','square','rectangle'] as $s)
                                    <option value="{{ $s }}" @selected(old('shape', $table->shape ?? 'square') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Not</label>
                            <input type="text" name="notes" class="form-control"
                                   value="{{ old('notes', $table->notes ?? '') }}">
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_reservable" value="1"
                                       @checked(old('is_reservable', $table->is_reservable ?? true))>
                                <label class="form-check-label">Rezerve edilebilir</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn" style="background:var(--color-sea);color:#fff;">Kaydet</button>
                    <a href="{{ route('admin.tables.index') }}" class="btn btn-outline-secondary">İptal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
