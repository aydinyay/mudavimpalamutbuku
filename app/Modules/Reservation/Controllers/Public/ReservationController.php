<?php

namespace App\Modules\Reservation\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Reservation\Mail\ReservationAdminNotification;
use App\Modules\Reservation\Mail\ReservationConfirmed;
use App\Modules\Reservation\Models\Reservation;
use App\Modules\Reservation\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function __construct(private AvailabilityService $availability) {}

    public function create()
    {
        return view('reservation.public.create');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'reservation_date' => 'required|date|after_or_equal:today',
            'arrival_time'     => 'required|date_format:H:i',
            'guest_count'      => 'required|integer|min:1|max:20',
        ]);

        $tables = $this->availability->availableTables(
            $request->reservation_date,
            $request->arrival_time,
            $request->integer('guest_count'),
        );

        return response()->json([
            'available' => $tables->isNotEmpty(),
            'table_id'  => $tables->first()?->id,
            'tables'    => $tables->map(fn($t) => [
                'id'         => $t->id,
                'label'      => $t->table_number,
                'area'       => $t->area->name(),
                'seats_max'  => $t->seats_max,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name'      => 'required|string|max:100',
            'guest_phone'     => 'required|string|max:20',
            'guest_email'     => 'nullable|email',
            'guest_count'     => 'required|integer|min:1|max:20',
            'reservation_date'=> 'required|date|after_or_equal:today',
            'arrival_time'    => 'required|date_format:H:i',
            'table_id'        => 'nullable|exists:tables,id',
            'special_requests'=> 'nullable|string|max:500',
            'source'          => 'in:website,boat',
        ]);

        $data['guest_locale'] = app()->getLocale();
        $data['status']       = 'pending';
        $data['type']         = 'table';
        $data['source']       = $data['source'] ?? 'website';

        // Auto-assign table if not selected
        if (empty($data['table_id'])) {
            $tables = $this->availability->availableTables(
                $data['reservation_date'],
                $data['arrival_time'],
                $data['guest_count'],
            );
            if ($tables->isEmpty()) {
                return back()->withErrors(['date' => __('reservation.no_availability')])->withInput();
            }
            $data['table_id'] = $tables->first()->id;
        }

        $reservation = Reservation::create($data);

        // Misafir onay maili (e-posta verdiyse)
        if ($reservation->guest_email) {
            try {
                Mail::to($reservation->guest_email)
                    ->send(new ReservationConfirmed($reservation));
            } catch (\Throwable) {}
        }

        // Admin bildirim maili
        try {
            Mail::to(config('mail.from.address'))
                ->send(new ReservationAdminNotification($reservation));
        } catch (\Throwable) {}

        return redirect()->route('reservation.public.show', $reservation->reservation_uuid)
            ->with('success', __('reservation.success_message'));
    }

    public function show(string $uuid)
    {
        $reservation = Reservation::where('reservation_uuid', $uuid)
            ->with(['table.area'])
            ->firstOrFail();

        return view('reservation.public.show', compact('reservation'));
    }

    public function cancelForm(string $uuid)
    {
        $reservation = Reservation::where('reservation_uuid', $uuid)
            ->whereIn('status', ['pending', 'confirmed'])
            ->firstOrFail();

        return view('reservation.public.cancel', compact('reservation'));
    }

    public function cancel(Request $request, string $uuid)
    {
        $reservation = Reservation::where('reservation_uuid', $uuid)
            ->whereIn('status', ['pending', 'confirmed'])
            ->firstOrFail();

        $reservation->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $request->input('reason'),
        ]);

        return redirect()->route('website.home')
            ->with('success', 'Rezervasyonunuz iptal edildi.');
    }
}
