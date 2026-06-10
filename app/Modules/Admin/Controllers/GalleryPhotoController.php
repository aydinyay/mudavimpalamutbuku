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

    // cPanel'de public_path() ≠ web root (public_html). DOCUMENT_ROOT kullan.
    private function galleryDir(): string
    {
        $root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
        $dir  = $root ? $root . DIRECTORY_SEPARATOR . 'gallery' : public_path('gallery');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function saveImage($uploadedFile): string
    {
        $dir      = $this->galleryDir();
        $filename = uniqid('gallery_') . '.webp';
        $manager  = new ImageManager(new Driver());
        $manager->read($uploadedFile)->cover(1200, 900)->toWebp(85)->save($dir . DIRECTORY_SEPARATOR . $filename);
        return $filename;
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

        $filename     = $this->saveImage($request->file('photo'));
        $hasMultilang = \Illuminate\Support\Facades\Schema::hasColumn('gallery_photos', 'alt_tr');

        GalleryPhoto::create(array_merge([
            'filename'   => $filename,
            'alt'        => $request->alt_tr ?? '',
            'caption'    => $request->alt_tr ?? '',
            'sort_order' => $request->integer('sort_order', 0),
            'active'     => true,
        ], $hasMultilang ? [
            'alt_tr' => $request->alt_tr,
            'alt_en' => $request->alt_en,
            'alt_de' => $request->alt_de,
        ] : []));

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
            $dir = $this->galleryDir();
            @unlink($dir . DIRECTORY_SEPARATOR . $photo->filename);
            $photo->filename = $this->saveImage($request->file('photo'));
        }

        $hasMultilang = \Illuminate\Support\Facades\Schema::hasColumn('gallery_photos', 'alt_tr');

        $photo->update(array_merge([
            'filename'   => $photo->filename,
            'alt'        => $request->alt_tr ?? '',
            'caption'    => $request->alt_tr ?? '',
            'sort_order' => $request->integer('sort_order', 0),
            'active'     => $request->boolean('active'),
        ], $hasMultilang ? [
            'alt_tr' => $request->alt_tr,
            'alt_en' => $request->alt_en,
            'alt_de' => $request->alt_de,
        ] : []));

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Fotoğraf güncellendi.');
    }

    public function bulkCreate()
    {
        return view('admin.gallery.bulk');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:8192',
        ]);

        $filename     = $this->saveImage($request->file('photo'));
        $hasMultilang = \Illuminate\Support\Facades\Schema::hasColumn('gallery_photos', 'alt_tr');

        $photo = GalleryPhoto::create(array_merge([
            'filename'   => $filename,
            'alt'        => '',
            'caption'    => '',
            'sort_order' => 0,
            'active'     => true,
        ], $hasMultilang ? ['alt_tr' => '', 'alt_en' => '', 'alt_de' => ''] : []));

        return response()->json([
            'ok'  => true,
            'id'  => $photo->id,
            'url' => asset('gallery/' . $filename),
        ]);
    }

    public function destroy(GalleryPhoto $photo)
    {
        @unlink($this->galleryDir() . DIRECTORY_SEPARATOR . $photo->filename);
        $photo->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Fotoğraf silindi.');
    }
}
