<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\RestaurantSetting;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SiteMediaController extends Controller
{
    private const AREAS = [
        'hero_bg_image' => [
            'label'  => 'Anasayfa Hero Arka Planı',
            'desc'   => 'Anasayfa tam ekran arka plan fotoğrafı (gradient üzerine hafif overlay olarak görünür)',
            'width'  => 1920,
            'height' => 1080,
            'icon'   => 'bi-house',
        ],
        'about_image' => [
            'label'  => 'Hakkımızda Görseli',
            'desc'   => 'Hakkımızda sayfasının sol tarafındaki fotoğraf',
            'width'  => 800,
            'height' => 600,
            'icon'   => 'bi-image',
        ],
    ];

    public function index()
    {
        $setting = RestaurantSetting::current();
        $areas   = self::AREAS;
        return view('admin.media.index', compact('setting', 'areas'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'key'   => 'required|in:' . implode(',', array_keys(self::AREAS)),
            'image' => 'required|image|max:10240',
        ]);

        $key  = $request->input('key');
        $area = self::AREAS[$key];

        $webRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? public_path(), '/');
        $dir     = $webRoot . '/images/site';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $manager = new ImageManager(new Driver());
        $img     = $manager->read($request->file('image'));
        $img->cover($area['width'], $area['height']);

        $filename = $key . '_' . time() . '.webp';
        $path     = 'images/site/' . $filename;
        $img->toWebp(85)->save($webRoot . '/' . $path);

        // Eski dosyayı sil
        $old = RestaurantSetting::current()->$key;
        if ($old && file_exists($webRoot . '/' . ltrim($old, '/'))) {
            @unlink($webRoot . '/' . ltrim($old, '/'));
        }

        RestaurantSetting::current()->update([$key => '/' . $path]);

        return back()->with('success', $area['label'] . ' güncellendi.');
    }

    public function delete(Request $request)
    {
        $key = $request->input('key');
        if (! array_key_exists($key, self::AREAS)) {
            return back()->with('error', 'Geçersiz alan.');
        }

        $setting = RestaurantSetting::current();
        $old     = $setting->$key;
        if ($old && file_exists(public_path(ltrim($old, '/')))) {
            @unlink(public_path(ltrim($old, '/')));
        }

        $setting->update([$key => null]);

        return back()->with('success', 'Görsel kaldırıldı.');
    }
}
