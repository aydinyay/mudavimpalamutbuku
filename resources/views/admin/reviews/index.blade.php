@extends('layouts.admin')

@section('title', 'Yorumlar & Şikayetler')
@section('page_title', 'Yorumlar & Şikayetler')

@section('content')

{{-- Özet kartları --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px;">
            <div style="font-size:2rem;font-weight:700;color:#1a6b5e;">{{ $summary['rating'] }}</div>
            <div style="color:#f59e0b;font-size:1.1rem;">
                @for($i=1;$i<=5;$i++){{ $i <= round($summary['rating']) ? '★' : '☆' }}@endfor
            </div>
            <div class="text-muted small mt-1">Google Puanı</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px;">
            <div style="font-size:2rem;font-weight:700;color:#1a6b5e;">{{ number_format($summary['total']) }}</div>
            <div class="text-muted small mt-1">Toplam Google Yorumu</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px;">
            <div style="font-size:2rem;font-weight:700;color:{{ $pendingComplaints->where('admin_read',false)->count() > 0 ? '#dc3545' : '#1a6b5e' }};">
                {{ $pendingComplaints->where('admin_read', false)->count() }}
            </div>
            <div class="text-muted small mt-1">Okunmamış Şikayet</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Sol: Negatif Google yorumları --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <span class="fw-600" style="font-size:.9rem;">
                    <i class="bi bi-star-half me-1 text-warning"></i>Düşük Google Yorumları (1–3★)
                </span>
                <div class="d-flex gap-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.72rem;">
                        {{ $negativeReviews->where('admin_read',false)->count() }} yeni
                    </span>
                    <form method="POST" action="{{ route('admin.reviews.sync') }}" class="d-inline">
                        @csrf <input type="hidden" name="source" value="google">
                        <button class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:2px 8px;">
                            <i class="bi bi-arrow-clockwise"></i> Sync
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($negativeReviews as $review)
                <div class="p-3 border-bottom {{ !$review->admin_read ? 'bg-danger bg-opacity-5' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-600" style="font-size:.85rem;">{{ $review->author_name }}</div>
                            <div style="color:#dc3545;font-size:.8rem;">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</div>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $review->review_time->format('d.m.Y') }}</div>
                    </div>
                    @if($review->comment)
                    <p class="mt-2 mb-2 text-muted" style="font-size:.82rem;font-style:italic;">
                        "{{ mb_substr($review->comment,0,200) }}{{ mb_strlen($review->comment)>200?'…':'' }}"
                    </p>
                    @endif
                    @if(!$review->admin_read)
                    <form method="POST" action="{{ route('admin.reviews.read', $review) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:2px 10px;">
                            Okundu İşaretle
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-emoji-smile d-block fs-3 mb-2"></i>Düşük puan yok — harika!
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sağ: İç anket şikayetleri --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-600" style="font-size:.9rem;">
                    <i class="bi bi-chat-left-text me-1 text-danger"></i>İç Anket Şikayetleri (1–3★)
                </span>
            </div>
            <div class="card-body p-0">
                @forelse($pendingComplaints as $survey)
                <div class="p-3 border-bottom {{ !$survey->admin_read ? 'bg-warning bg-opacity-10' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-600" style="font-size:.85rem;">{{ $survey->guest_name ?? 'İsimsiz' }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $survey->guest_phone }}</div>
                        </div>
                        <div class="text-end">
                            <div style="color:#dc3545;font-size:.8rem;">{{ str_repeat('★',$survey->rating??0) }}{{ str_repeat('☆',5-($survey->rating??0)) }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $survey->responded_at?->format('d.m.Y H:i') ?? '—' }}</div>
                        </div>
                    </div>
                    @if($survey->feedback)
                    <p class="mt-2 mb-2 text-muted" style="font-size:.82rem;font-style:italic;">
                        "{{ $survey->feedback }}"
                    </p>
                    @endif
                    @if(!$survey->admin_read)
                    <form method="POST" action="{{ route('admin.complaints.read', $survey) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:2px 10px;">
                            Okundu İşaretle
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-emoji-smile d-block fs-3 mb-2"></i>Şikayet yok — harika!
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if($lastSync)
<p class="text-muted small mt-3 text-end">
    Son sync: {{ $lastSync->synced_at->diffForHumans() }} —
    {{ $lastSync->status === 'ok' ? '✓ Başarılı' : '✗ Hata: '.$lastSync->error }}
</p>
@endif

@endsection
