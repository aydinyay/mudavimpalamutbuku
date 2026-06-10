@extends('layouts.admin')

@section('title', 'Günlük Özel')
@section('page_title', 'Günlük Özel — Bugün Ne Var?')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Restoran için günlük özel yemek / menü kaydı.</p>
    </div>
    <a href="{{ route('admin.daily-specials.create') }}" class="btn btn-sm"
       style="background:#1a7fa8;color:#fff;border-radius:8px;">
        <i class="bi bi-plus-lg me-1"></i> Yeni Ekle
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="px-4 py-3">Tarih</th>
                        <th class="px-4 py-3">Başlık (TR)</th>
                        <th class="px-4 py-3">Fiyat</th>
                        <th class="px-4 py-3">Durum</th>
                        <th class="px-4 py-3 text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($specials as $special)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="fw-500">{{ $special->date->format('d.m.Y') }}</span>
                            @if($special->date->isToday())
                                <span class="badge ms-1" style="background:#2ea887;font-size:.7rem;">BUGÜN</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $special->title_tr }}</td>
                        <td class="px-4 py-3">
                            @if($special->price)
                                {{ number_format($special->price, 0, ',', '.') }} ₺
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($special->is_active)
                                <span class="badge" style="background:#2ea887;">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('admin.daily-specials.edit', $special) }}"
                               class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.daily-specials.destroy', $special) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Bu kaydı silmek istediğinizden emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                            Henüz günlük özel eklenmemiş.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($specials->hasPages())
<div class="mt-3 d-flex justify-content-center">
    {{ $specials->links() }}
</div>
@endif

@endsection
