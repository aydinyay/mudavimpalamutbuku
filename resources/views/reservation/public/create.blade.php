@extends('layouts.website')

@section('title', __('reservation.title'))

@section('content')
<div style="padding-top:80px;background:var(--color-light);min-height:calc(100vh - 80px);">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-4">
                <h1 class="section-title">{{ __('reservation.make_reservation') }}</h1>
                <div class="section-divider"></div>
            </div>

            @if(!$onlineEnabled)
                @php
                    $whatsAppText = "Merhaba, rezervasyon talebinde bulunmak istiyorum.\nAd Soyad:\nTarih:\nSaat:\nKişi Sayısı:";
                    $whatsAppUrl = 'https://wa.me/' . config('reservation.whatsapp_notify_number') . '?text=' . urlencode($whatsAppText);
                @endphp
                <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
                    <div class="card-body p-4 text-center">
                        <p class="mb-4">Yoğunluk nedeniyle şu anda sadece WhatsApp üzerinden rezervasyon talebi alabiliyoruz.</p>
                        <a href="{{ $whatsAppUrl }}" class="btn btn-lg w-100" style="background:var(--color-sea);color:#fff;border-radius:16px;font-weight:700;padding:14px;" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp me-1"></i>WhatsApp'tan Talep Et
                        </a>
                    </div>
                </div>
            @else
            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('reservation.public.store') }}" method="POST" id="reservationForm">
                @csrf

                {{-- Step 1: Date, time, guests --}}
                <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h6 class="fw-700 mb-3" style="color:var(--color-sea);">1. {{ __('reservation.date') }} & {{ __('reservation.guests') }}</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-600">{{ __('reservation.date') }}</label>
                                <input type="date" name="reservation_date" class="form-control" required
                                       value="{{ old('reservation_date', $defaultDate) }}"
                                       min="{{ $minDate }}"
                                       max="{{ $maxDate }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-600">{{ __('reservation.time') }}</label>
                                <select name="arrival_time" class="form-select" required>
                                    <option value="">Seçin</option>
                                    @foreach(['12:00','12:30','13:00','13:30','14:00','14:30','15:00','18:00','18:30','19:00','19:30','20:00','20:30','21:00','21:30','22:00','22:30','23:00'] as $t)
                                        <option value="{{ $t }}" @selected(old('arrival_time', '19:00') === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-600">{{ __('reservation.guests') }}</label>
                                <select name="guest_count" class="form-select" required>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" @selected(old('guest_count', 2) == $i)>{{ $i }} kişi</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-6 d-flex align-items-end">
                                <button type="button" id="checkAvailability"
                                        class="btn w-100" style="background:var(--color-sea);color:#fff;border-radius:12px;">
                                    {{ __('reservation.check_availability') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Availability result --}}
                <div id="availabilityResult" class="mb-3 d-none">
                    <div class="alert alert-success" id="availableMsg">
                        <i class="bi bi-check-circle me-2"></i>Uygun masa bulundu!
                    </div>
                    <div class="alert alert-warning" id="unavailableMsg" style="display:none">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ __('reservation.no_availability') }}
                    </div>
                </div>

                {{-- Step 2: Guest info --}}
                <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;" id="guestInfoCard">
                    <div class="card-body p-4">
                        <h6 class="fw-700 mb-3" style="color:var(--color-sea);">2. {{ __('reservation.name') }} & {{ __('reservation.phone') }}</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-600">{{ __('reservation.name') }}</label>
                                <input type="text" name="guest_name" class="form-control"
                                       value="{{ old('guest_name') }}" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-600">{{ __('reservation.phone') }}</label>
                                <input type="tel" name="guest_phone" class="form-control"
                                       value="{{ old('guest_phone') }}" required placeholder="05xx xxx xx xx">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-600">{{ __('reservation.email') }} <span class="text-muted">(opsiyonel)</span></label>
                                <input type="email" name="guest_email" class="form-control" value="{{ old('guest_email') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-600">{{ __('reservation.special_requests') }}</label>
                                <textarea name="special_requests" class="form-control" rows="2"
                                          placeholder="Alerji, doğum günü, özel istek...">{{ old('special_requests') }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="source" value="boat" id="byBoat"
                                           @checked(old('source') === 'boat')>
                                    <label class="form-check-label" for="byBoat">
                                        <i class="bi bi-water me-1"></i>{{ __('reservation.arriving_by_boat') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-lg w-100" style="background:var(--color-coral);color:#fff;border-radius:16px;font-weight:700;padding:14px;">
                    {{ __('reservation.confirm') }} <i class="bi bi-check-circle ms-1"></i>
                </button>
            </form>
            @endif

            <p class="text-center text-muted small mt-3">
                <i class="bi bi-telephone me-1"></i>
                Telefon ile: <a href="tel:{{ config('restaurant.phone') }}">{{ config('restaurant.phone') }}</a>
            </p>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
@if($onlineEnabled)
<script>
document.getElementById('checkAvailability').addEventListener('click', async () => {
    const date  = document.querySelector('[name="reservation_date"]').value;
    const time  = document.querySelector('[name="arrival_time"]').value;
    const count = document.querySelector('[name="guest_count"]').value;

    if (!date || !time) {
        alert('Lütfen tarih ve saat seçin.');
        return;
    }

    const res = await fetch('{{ route('reservation.availability') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ reservation_date: date, arrival_time: time, guest_count: count }),
    });

    const resultDiv = document.getElementById('availabilityResult');
    resultDiv.classList.remove('d-none');

    if (!res.ok) {
        document.getElementById('availableMsg').style.display = 'none';
        document.getElementById('unavailableMsg').style.display = '';
        return;
    }

    const data = await res.json();

    if (data.available) {
        document.getElementById('availableMsg').style.display = '';
        document.getElementById('unavailableMsg').style.display = 'none';
    } else {
        document.getElementById('availableMsg').style.display = 'none';
        document.getElementById('unavailableMsg').style.display = '';
    }
});
</script>
@endif
@endpush
