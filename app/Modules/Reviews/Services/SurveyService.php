<?php

namespace App\Modules\Reviews\Services;

use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SurveyService
{
    private const GOOGLE_REVIEW_URL = 'https://g.page/r/Cd4zYQe_40RuEBM/review';

    /**
     * Rezervasyon kapanışında: token üretir, WhatsApp/SMS linki döner.
     */
    public function createSurvey(string $guestName, string $guestPhone, ?int $reservationId = null): SurveyResponse
    {
        return SurveyResponse::create([
            'token'          => Str::random(48),
            'guest_name'     => $guestName,
            'guest_phone'    => $guestPhone,
            'reservation_id' => $reservationId,
            'sent_at'        => now(),
        ]);
    }

    /**
     * Müşteri anketi yanıtladığında çağrılır.
     * 4-5 yıldız → Google'a yönlendir
     * 1-3 yıldız → Şikayeti admin'e düşür, Google linki verme
     */
    public function handleResponse(SurveyResponse $survey, int $rating, ?string $feedback = null): string
    {
        $outcome = $rating >= 4 ? 'sent_to_google' : 'sent_to_admin';

        $survey->update([
            'rating'       => $rating,
            'feedback'     => $feedback,
            'outcome'      => $outcome,
            'responded_at' => now(),
        ]);

        if ($outcome === 'sent_to_admin') {
            $this->notifyAdminComplaint($survey);
        }

        return $outcome === 'sent_to_google'
            ? self::GOOGLE_REVIEW_URL
            : route('survey.thank_you', ['type' => 'feedback']);
    }

    private function notifyAdminComplaint(SurveyResponse $survey): void
    {
        $adminEmail = config('mail.admin_email', 'aydinyay@gmail.com');
        try {
            Mail::send('emails.reviews.survey-complaint', ['survey' => $survey], function ($m) use ($adminEmail, $survey) {
                $m->to($adminEmail)
                  ->subject('📋 Yeni Müşteri Şikayeti — ' . $survey->rating . '★ ' . $survey->guest_name);
            });
        } catch (\Throwable $e) {
            Log::error('Survey complaint mail failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * WhatsApp mesaj linki üretir (wa.me deep link).
     * Garsonun cihazından tek tıkla gönderilebilir.
     */
    public function whatsappLink(SurveyResponse $survey): string
    {
        $surveyUrl  = route('survey.show', $survey->token);
        $restaurant = config('restaurant.name.tr', 'Müdavim Restaurant');
        $message    = "Merhaba {$survey->guest_name}! {$restaurant}'daki deneyiminizi değerlendirir misiniz? (1 dakika sürer) 👉 {$surveyUrl}";

        return 'https://wa.me/' . ltrim($survey->guest_phone, '+0') . '?text=' . urlencode($message);
    }

    public function pendingAdminCount(): int
    {
        return SurveyResponse::where('outcome', 'sent_to_admin')
            ->where('admin_read', false)
            ->count();
    }
}
