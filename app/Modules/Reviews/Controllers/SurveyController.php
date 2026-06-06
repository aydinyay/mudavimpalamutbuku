<?php

namespace App\Modules\Reviews\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use App\Modules\Reviews\Services\SurveyService;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function __construct(private SurveyService $service) {}

    /** Anket sayfası: /anket/{token} */
    public function show(string $token)
    {
        $survey = SurveyResponse::where('token', $token)
            ->whereNull('responded_at')           // Zaten yanıtlanmışsa gösterme
            ->firstOrFail();

        return view('survey.show', compact('survey'));
    }

    /** Müşteri puanı seçip gönderdi */
    public function submit(Request $request, string $token)
    {
        $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $survey = SurveyResponse::where('token', $token)
            ->whereNull('responded_at')
            ->firstOrFail();

        $redirectUrl = $this->service->handleResponse(
            $survey,
            (int) $request->rating,
            $request->feedback
        );

        // 4-5 yıldız → Google review sayfasına yönlendir
        // 1-3 yıldız → teşekkür sayfasına (Google linki YOK)
        return redirect()->away($redirectUrl);
    }

    /** Teşekkür sayfası (düşük puan sonrası) */
    public function thankYou(Request $request)
    {
        return view('survey.thank_you', ['type' => $request->type]);
    }
}
