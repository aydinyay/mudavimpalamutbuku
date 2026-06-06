<?php

namespace App\Modules\Reviews\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reservation\Models\Reservation;
use App\Modules\Reviews\Services\SurveyService;

class SurveyAdminController extends Controller
{
    public function __construct(private SurveyService $service) {}

    /**
     * Rezervasyon için anket oluştur ve WhatsApp linkini aç.
     * Garson admin panelinden yeşil butona basar → WhatsApp açılır.
     */
    public function whatsappLink(Reservation $reservation)
    {
        $survey = $this->service->createSurvey(
            $reservation->guest_name,
            $reservation->guest_phone,
            $reservation->id
        );

        return redirect()->away($this->service->whatsappLink($survey));
    }
}
