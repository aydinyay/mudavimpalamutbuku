@extends('layouts.website')

@section('title', 'Deneyiminizi Değerlendirin')

@push('styles')
<style>
.star-rating{display:flex;flex-direction:row-reverse;justify-content:center;gap:8px;margin:16px 0}
.star-rating input{display:none}
.star-rating label{font-size:2.8rem;color:#ddd;cursor:pointer;transition:color .15s}
.star-rating label:hover,.star-rating label:hover~label,.star-rating input:checked~label{color:#f59e0b}
</style>
@endpush

@section('content')
<div style="padding-top:80px;background:var(--color-light);min-height:calc(100vh - 80px);">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card border-0 shadow-sm text-center" style="border-radius:20px;">
                <div class="card-body p-5">
                    <img src="{{ asset('images/logo-dark.png') }}" alt="Müdavim" style="height:48px;width:auto;margin-bottom:20px;">
                    <h4 class="fw-700 mb-1">Nasıldı?</h4>
                    <p class="text-muted small mb-4">
                        Merhaba {{ $survey->guest_name ?? 'Değerli Misafirimiz' }},<br>
                        Müdavim'deki deneyiminizi 1 dakikada değerlendirir misiniz?
                    </p>

                    <form action="{{ route('survey.submit', $survey->token) }}" method="POST" id="surveyForm">
                        @csrf
                        <div class="star-rating" id="starRating">
                            @for($i=5;$i>=1;$i--)
                            <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" required>
                            <label for="star{{ $i }}" title="{{ $i }} yıldız">★</label>
                            @endfor
                        </div>
                        <p class="text-muted small mb-4" id="ratingHint">Bir puan seçin</p>

                        <div id="feedbackArea" style="display:none;">
                            <textarea name="feedback" class="form-control mb-3" rows="3"
                                placeholder="Deneyiminizi paylaşır mısınız? (isteğe bağlı)"
                                style="border-radius:10px;font-size:.88rem;resize:none;"></textarea>
                        </div>

                        <button type="submit" class="btn w-100 fw-600" id="submitBtn" disabled
                                style="background:var(--color-sea);color:#fff;border-radius:12px;padding:12px;">
                            Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
var hints = ['','Çok kötü','Kötü','Fena değil','İyi','Harika! 🎉'];
document.querySelectorAll('.star-rating input').forEach(function(inp){
    inp.addEventListener('change', function(){
        var r = parseInt(this.value);
        document.getElementById('ratingHint').textContent = hints[r];
        document.getElementById('submitBtn').disabled = false;
        // Düşük puan → yorum kutusu göster
        document.getElementById('feedbackArea').style.display = (r <= 3) ? 'block' : 'none';
    });
});
</script>
@endpush
