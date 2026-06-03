@extends('layouts.website')

@section('title', __('reservation.cancel_reservation'))

@section('content')
<div style="padding-top:80px;background:var(--color-light);min-height:calc(100vh - 80px);">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:20px;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-2"></i>
                        <h4 class="fw-700">{{ __('reservation.cancel_reservation') }}</h4>
                    </div>
                    <div class="bg-light rounded p-3 mb-4 small">
                        <strong>{{ $reservation->guest_name }}</strong><br>
                        {{ $reservation->reservation_date->format('d.m.Y') }} — {{ $reservation->arrival_time }}<br>
                        {{ $reservation->guest_count }} kişi
                    </div>
                    <form method="POST" action="{{ route('reservation.public.cancel', $reservation->reservation_uuid) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('reservation.cancellation_reason') }}</label>
                            <textarea name="reason" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" style="border-radius:12px;">
                            Evet, İptal Et
                        </button>
                        <a href="{{ route('reservation.public.show', $reservation->reservation_uuid) }}"
                           class="btn btn-outline-secondary w-100 mt-2" style="border-radius:12px;">
                            Vazgeç
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
