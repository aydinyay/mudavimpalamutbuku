<?php

namespace App\Modules\Reviews\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstagramPost;
use App\Models\ReviewSyncLog;
use App\Modules\Reviews\Services\InstagramService;
use Illuminate\Http\Request;

class InstagramAdminController extends Controller
{
    public function index()
    {
        $posts   = InstagramPost::orderByDesc('posted_at')->paginate(20);
        $lastSync = ReviewSyncLog::where('source', 'instagram')->latest('synced_at')->first();
        $hasToken = ! empty(config('services.instagram.access_token'));

        return view('admin.instagram.index', compact('posts', 'lastSync', 'hasToken'));
    }

    public function toggle(InstagramPost $post)
    {
        $post->update(['is_visible' => ! $post->is_visible]);
        return back()->with('success', $post->is_visible ? 'Görselde yayında.' : 'Gizlendi.');
    }

    public function syncNow(InstagramService $service)
    {
        $result = $service->sync();
        return back()->with('success', "Sync tamamlandı. Yeni gönderi: {$result['new']}");
    }

    public function refreshToken(InstagramService $service)
    {
        try {
            $service->refreshToken();
            return back()->with('success', 'Instagram token yenilendi (60 gün geçerli).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Token yenileme başarısız: ' . $e->getMessage());
        }
    }
}
