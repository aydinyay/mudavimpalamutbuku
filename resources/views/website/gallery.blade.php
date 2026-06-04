@extends('layouts.website')

@section('title', 'Galeri')

@section('content')
<div style="padding-top:80px;"></div>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title">Galeri</h1>
            <div class="section-divider"></div>
        </div>
        <div class="row g-3">
            {{-- Fotoğraflar admin panelinden eklenince burada görünecek --}}
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-images fs-1 d-block mb-3"></i>
                <p>Fotoğraflar yakında eklenecek.</p>
            </div>
        </div>
    </div>
</section>
@endsection
