<?php

namespace App\Modules\Reviews\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleReview;
use App\Models\SurveyResponse;
use App\Modules\Reviews\Services\GoogleReviewService;
use App\Modules\Reviews\Services\InstagramService;
use Illuminate\Http\Request;

class ReviewAdminController extends Controller
{
    public function index(GoogleReviewService $googleService)
    {
        $negativeReviews   = GoogleReview::where('rating', '<=', 3)
            ->orderByDesc('review_time')->get();
        $pendingComplaints = SurveyResponse::where('outcome', 'sent_to_admin')
            ->orderByDesc('created_at')->get();
        $summary           = $googleService->getSummary();
        $lastSync          = \App\Models\ReviewSyncLog::where('source', 'google')
            ->latest('synced_at')->first();

        return view('admin.reviews.index', compact(
            'negativeReviews', 'pendingComplaints', 'summary', 'lastSync'
        ));
    }

    /** Admin negatif yorumu inceledi */
    public function markRead(GoogleReview $review)
    {
        $review->update(['admin_read' => true]);
        return back()->with('success', 'Okundu olarak işaretlendi.');
    }

    /** Admin şikayeti inceledi */
    public function markComplaintRead(SurveyResponse $survey)
    {
        $survey->update(['admin_read' => true]);
        return back()->with('success', 'Şikayet okundu.');
    }

    /** Manuel sync tetikle (admin panelinden) */
    public function syncNow(Request $request, GoogleReviewService $google, InstagramService $instagram)
    {
        $source = $request->input('source', 'google');

        $result = match($source) {
            'google'    => $google->sync(),
            'instagram' => $instagram->sync(),
            default     => abort(400),
        };

        return back()->with('success', "Sync tamamlandı. Yeni: {$result['new']}");
    }
}
