<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GalleryPhotoController extends Controller
{
    public function index()
    {
        $photos = GalleryPhoto::orderBy('sort_order')->orderBy('id', 'desc')->get();
        return view('admin.gallery.index', compact('photos'));
    }

    public function create()
    {
        return view('admin.gallery.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo'      => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'alt_tr'     => 'nullable|string|max:200',
            'alt_en'     => 'nullable|string|max:200',
            'alt_de'     => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $galleryDir = public_path('gallery');
        if (!is_dir($galleryDir)) {
            mkdir($galleryDir, 0755, true);
        }

        $filename = uniqid('gallery_') . '.webp';
        $savePath = $galleryDir . DIRECTORY_SEPARATOR . $filename;

        $manager = new ImageManager(new Driver());
        $img = $manager->read($request->file('photo'));
        $img->cover(1200, 900);
        $img->toWebp(85)->save($savePath);

        GalleryPhoto::create([
            'filename'   => $filename,
            'alt_tr'     => $request->alt_tr,
            'alt_en'     => $request->alt_en,
            'alt_de'     => $request->alt_de,
            'sort_order' => $request->integer('sort_order', 0),
            'active'     => true,
        ]);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Fotoğraf başarıyla eklendi.');
    }

    public function edit(GalleryPhoto $photo)
    {
        return view('admin.gallery.form', compact('photo'));
    }

    public function update(Request $request, GalleryPhoto $photo)
    {
        $request->validate([
            'photo'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'alt_tr'     => 'nullable|string|max:200',
            'alt_en'     => 'nullable|string|max:200',
            'alt_de'     => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            $galleryDir = public_path('gallery');
            if (!is_dir($galleryDir)) {
                mkdir($galleryDir, 0755, true);
            }

            // Eski dosyayı sil
            $oldPath = $galleryDir . DIRECTORY_SEPARATOR . $photo->filename;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }

            $filename = uniqid('gallery_') . '.webp';
            $savePath = $galleryDir . DIRECTORY_SEPARATOR . $filename;

            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('photo'));
            $img->cover(1200, 900);
            $img->toWebp(85)->save($savePath);

            $photo->filename = $filename;
        }

        $photo->update([
            'filename'   => $photo->filename,
            'alt_tr'     => $request->alt_tr,
            'alt_en'     => $request->alt_en,
            'alt_de'     => $request->alt_de,
            'sort_order' => $request->integer('sort_order', 0),
            'active'     => $request->boolean('active'),
        ]);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Fotoğraf güncellendi.');
    }

    public function destroy(GalleryPhoto $photo)
    {
        $path = public_path('gallery' . DIRECTORY_SEPARATOR . $photo->filename);
        if (file_exists($path)) {
            @unlink($path);
        }

        $photo->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Fotoğraf silindi.');
    }
}
