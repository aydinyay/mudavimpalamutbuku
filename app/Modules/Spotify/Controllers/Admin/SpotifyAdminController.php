<?php

namespace App\Modules\Spotify\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\RestaurantSetting;
use App\Modules\Spotify\Services\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpotifyAdminController extends Controller
{
    public function __construct(private SpotifyService $spotify) {}

    public function index()
    {
        $setting   = RestaurantSetting::current();
        $connected = $this->spotify->isConnected();

        return view('admin.spotify.index', compact('setting', 'connected'));
    }

    public function auth()
    {
        $state = Str::random(16);
        session(['spotify_state' => $state]);
        return redirect($this->spotify->getAuthUrl($state));
    }

    public function callback(Request $request)
    {
        if ($request->get('state') !== session('spotify_state')) {
            return redirect()->route('admin.spotify.index')->with('error', 'Güvenlik hatası. Tekrar deneyin.');
        }

        if ($request->has('error')) {
            return redirect()->route('admin.spotify.index')->with('error', 'Spotify erişimi reddedildi.');
        }

        $ok = $this->spotify->handleCallback($request->get('code'));

        if ($ok) {
            RestaurantSetting::current()->update(['spotify_enabled' => true]);
            return redirect()->route('admin.spotify.index')->with('success', 'Spotify başarıyla bağlandı!');
        }

        return redirect()->route('admin.spotify.index')->with('error', 'Bağlantı başarısız. Tekrar deneyin.');
    }

    public function toggle(Request $request)
    {
        $this->spotify->setEnabled((bool) $request->input('enabled', false));
        return back()->with('success', 'Spotify durumu güncellendi.');
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'spotify_fallback_playlist_name' => 'nullable|string|max:100',
            'spotify_fallback_playlist_url'  => 'nullable|url',
            'spotify_widget_on_website'      => 'nullable|boolean',
            'spotify_widget_on_menu'         => 'nullable|boolean',
            'ambiance_page_active'           => 'nullable|boolean',
        ]);

        $data['spotify_widget_on_website'] = $request->boolean('spotify_widget_on_website');
        $data['spotify_widget_on_menu']    = $request->boolean('spotify_widget_on_menu');
        $data['ambiance_page_active']      = $request->boolean('ambiance_page_active');

        RestaurantSetting::current()->update($data);
        return back()->with('success', 'Ayarlar kaydedildi.');
    }

    public function disconnect()
    {
        $this->spotify->disconnect();
        return redirect()->route('admin.spotify.index')->with('success', 'Spotify bağlantısı kesildi.');
    }

    public function refresh()
    {
        $ok = $this->spotify->refreshAccessToken();
        return response()->json(['ok' => $ok]);
    }
}
