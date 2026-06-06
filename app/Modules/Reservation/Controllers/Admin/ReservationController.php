<?php

namespace App\Modules\Reservation\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reservation\Models\Reservation;
use App\Modules\TablePlan\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $date     = $request->get('tarih', Carbon::today()->toDateString());
        $selected = Carbon::parse($date);

        // Seçili günün rezervasyonları
        $reservations = Reservation::whereDate('reservation_date', $date)
            ->where('status', '!=', 'cancelled')
            ->with(['table.area'])
            ->orderBy('arrival_time')
            ->get();

        // Bölgeye göre dağılım
        $byArea = $reservations->groupBy(fn($r) => $r->table?->area?->name_tr ?? 'Masasız');

        // 60 günlük takvim için günlük sayılar (bugün dahil)
        $start  = Carbon::today();
        $end    = $start->copy()->addDays(59);
        $counts = Reservation::whereBetween('reservation_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(reservation_date) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        // Yaklaşan dolu günler (en kalabalık 5)
        $busyDays = Reservation::where('reservation_date', '>=', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(reservation_date) as day, COUNT(*) as cnt, SUM(guest_count) as guests')
            ->groupBy('day')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();

        return view('admin.reservation.index', compact(
            'reservations', 'date', 'selected', 'byArea', 'counts', 'busyDays', 'start'
        ));
    }

    public function create()
    {
        $tables = Table::where('is_active', true)->with('area')->orderBy('table_number')->get();
        return view('admin.reservation.form', compact('tables'));
    }

    public function quick()
    {
        return view('admin.reservation.quick');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name'      => 'required|string|max:100',
            'guest_phone'     => 'nullable|string|max:20',
            'guest_email'     => 'nullable|email',
            'guest_count'     => 'required|integer|min:1',
            'reservation_date'=> 'required|date',
            'arrival_time'    => 'required|date_format:H:i',
            'table_id'        => 'nullable|exists:tables,id',
            'special_requests'=> 'nullable|string',
            'source'          => 'required|in:website,admin,phone,walkin,boat',
            'internal_notes'  => 'nullable|string',
        ]);

        $data['status'] = 'confirmed';
        $data['type']   = 'table';
        Reservation::create($data);

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Rezervasyon eklendi.');
    }

    public function show(Reservation $reservation)
    {
        return view('admin.reservation.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        $tables = Table::where('is_active', true)->with('area')->orderBy('table_number')->get();
        return view('admin.reservation.form', compact('reservation', 'tables'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'guest_name'      => 'required|string|max:100',
            'guest_phone'     => 'nullable|string|max:20',
            'guest_email'     => 'nullable|email',
            'guest_count'     => 'required|integer|min:1',
            'reservation_date'=> 'required|date',
            'arrival_time'    => 'required|date_format:H:i',
            'table_id'        => 'nullable|exists:tables,id',
            'special_requests'=> 'nullable|string',
            'source'          => 'required|in:website,admin,phone,walkin,boat',
            'internal_notes'  => 'nullable|string',
        ]);

        $reservation->update($data);
        return redirect()->route('admin.reservations.index')
            ->with('success', 'Rezervasyon güncellendi.');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        return redirect()->route('admin.reservations.index')
            ->with('success', 'Rezervasyon iptal edildi.');
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate(['status' => 'required|in:pending,confirmed,cancelled,no_show,completed']);

        $data = ['status' => $request->status];
        if ($request->status === 'confirmed') $data['confirmed_at'] = now();
        if ($request->status === 'cancelled')  $data['cancelled_at'] = now();

        $reservation->update($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'status' => $request->status]);
        }

        return back()->with('success', 'Durum güncellendi.');
    }
}
