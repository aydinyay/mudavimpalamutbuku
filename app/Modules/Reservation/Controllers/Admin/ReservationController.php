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
        $date = $request->get('tarih', Carbon::today()->toDateString());

        $reservations = Reservation::whereDate('reservation_date', $date)
            ->with(['table.area'])
            ->orderBy('arrival_time')
            ->get();

        return view('admin.reservation.index', compact('reservations', 'date'));
    }

    public function create()
    {
        $tables = Table::where('is_active', true)->with('area')->orderBy('table_number')->get();
        return view('admin.reservation.form', compact('tables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name'      => 'required|string|max:100',
            'guest_phone'     => 'required|string|max:20',
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
            'guest_phone'     => 'required|string|max:20',
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
