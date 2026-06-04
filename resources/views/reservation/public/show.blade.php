@extends('layouts.website')

@section('title', __('reservation.title'))

@section('content')
<div style="padding-top:80px;background:var(--color-light);min-height:calc(100vh - 80px);">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm text-center" style="border-radius:20px;">
                <div class="card-body p-5">
                    @if($reservation->status === 'cancelled')
                        <i class="bi bi-x-circle fs-1 text-danger mb-3 d-block"></i>
                        <h4 class="fw-700">Rezervasyon İptal Edildi</h4>
                    @else
                        <i class="bi bi-check-circle fs-1 mb-3 d-block" style="color:var(--color-sea);"></i>
                        <h4 class="fw-700">Rezervasyonunuz Alındı</h4>
                        @if(session('sms_sent') === true)
                            <p class="small mb-4" style="color:var(--color-sea);">
                                <i class="bi bi-phone-fill"></i> Onay SMS'i gönderildi.
                            </p>
                        @elseif(session('sms_sent') === false)
                            <p class="text-muted small mb-4">
                                <i class="bi bi-phone"></i> SMS gönderilemedi — rezervasyonunuz kayıt altında.
                            </p>
                        @else
                            <p class="text-muted small mb-4">Onay SMS'i gönderilecektir.</p>
                        @endif
                    @endif

                    <div class="bg-light rounded-3 p-3 text-start mt-4">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted">Ad</dt>
                            <dd class="col-7">{{ $reservation->guest_name }}</dd>
                            <dt class="col-5 text-muted">Telefon</dt>
                            <dd class="col-7">{{ $reservation->guest_phone }}</dd>
                            <dt class="col-5 text-muted">Tarih</dt>
                            <dd class="col-7">{{ $reservation->reservation_date->format('d.m.Y') }}</dd>
                            <dt class="col-5 text-muted">Saat</dt>
                            <dd class="col-7">{{ $reservation->arrival_time }}</dd>
                            <dt class="col-5 text-muted">Kişi Sayısı</dt>
                            <dd class="col-7">{{ $reservation->guest_count }}</dd>
                            @if($reservation->table)
                            <dt class="col-5 text-muted">Masa</dt>
                            <dd class="col-7">{{ $reservation->table->table_number }} ({{ $reservation->table->area->name() }})</dd>
                            @endif
                            <dt class="col-5 text-muted">Durum</dt>
                            <dd class="col-7">
                                <span class="status-badge {{ $reservation->statusBadgeClass() }}">
                                    {{ __('reservation.status_' . $reservation->status) }}
                                </span>
                            </dd>
                        </dl>
                    </div>

                    <div class="mt-4 small text-muted">
                        Rezervasyon No: <code>{{ substr($reservation->reservation_uuid, 0, 8) }}</code>
                    </div>

                    @if(in_array($reservation->status, ['pending', 'confirmed']))
                    <a href="{{ route('reservation.public.cancel.form', $reservation->reservation_uuid) }}"
                       class="btn btn-outline-danger btn-sm mt-3">
                        {{ __('reservation.cancel_reservation') }}
                    </a>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('website.home') }}" class="btn btn-sm" style="background:var(--color-sea);color:#fff;border-radius:12px;">
                            Ana Sayfa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
