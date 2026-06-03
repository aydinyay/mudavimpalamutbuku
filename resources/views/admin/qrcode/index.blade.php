@extends('layouts.admin')

@section('title', 'QR Kodlar')
@section('page_title', 'QR Kodlar')

@section('content')

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.qrcodes.print') }}" class="btn btn-outline-secondary">
        <i class="bi bi-printer me-1"></i>Tümünü PDF Yazdır
    </a>
</div>

@foreach($areas as $area)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <strong>{{ $area->name_tr }}</strong>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($area->tables as $table)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="border rounded p-3 text-center">
                    @if($table->qrCode && $table->qrCode->image_path)
                        <img src="{{ $table->qrCode->image_path }}" style="width:120px;height:120px;" alt="QR">
                        <div class="small text-muted mt-1">Tarama: {{ $table->qrCode->scan_count }}</div>
                    @else
                        <div style="width:120px;height:120px;background:#f0f0f0;border-radius:8px;margin:0 auto;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-qr-code fs-1 text-muted"></i>
                        </div>
                        <div class="small text-muted mt-1">Henüz üretilmedi</div>
                    @endif
                    <div class="fw-700 mt-2">{{ $table->table_number }}</div>
                    <form method="POST" action="{{ route('admin.qrcodes.generate', $table) }}" class="mt-2">
                        @csrf
                        <button class="btn btn-sm w-100" style="background:var(--color-coral);color:#fff;border-radius:8px;">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            {{ $table->qrCode ? 'Yenile' : 'QR Üret' }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach

@endsection
