@extends('layouts.website')
@section('title', 'Ambiyans Test')

@push('styles')
<style>
/* STEP 1: Just dark background */
body { background: #020610 !important; color: #fff; }

/* Uncomment steps ONE AT A TIME to test:

STEP 2: Add html overflow rules (these were in original CSS)
html { overflow-y: scroll; overflow-x: hidden; }
body { width: 100%; overflow-x: hidden; }

STEP 3: Add will-change and transitions
#sky { will-change: background; transition: background 12s linear; }

STEP 4: Add backdrop-filter
.glass-section { backdrop-filter: blur(20px); }

*/
</style>
@endpush

@section('content')
{{-- STEP 5: Add sky canvas --}}
{{-- <div id="sky" style="position:fixed;inset:0;z-index:0;background:#020610;"></div> --}}
{{-- <canvas id="star-canvas" style="position:fixed;inset:0;z-index:1;pointer-events:none;"></canvas> --}}

<div style="height:300vh;display:flex;align-items:flex-start;justify-content:center;padding-top:120px;">
    <p style="color:#fff;font-size:1.5rem;">Ambiyans Test — Şu an aktif olan CSS/HTML kademeleri yukarıda gösterilmiştir.</p>
</div>
@endsection
