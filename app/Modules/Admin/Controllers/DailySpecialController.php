<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailySpecial;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DailySpecialController extends Controller
{
    public function index(): View
    {
        $specials = DailySpecial::orderBy('date', 'desc')->paginate(20);
        return view('admin.daily-special.index', compact('specials'));
    }

    public function create(): View
    {
        return view('admin.daily-special.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->saveImage($request->file('image'));
        }

        DailySpecial::create($data);

        return redirect()->route('admin.daily-specials.index')
            ->with('success', 'Günlük özel başarıyla eklendi.');
    }

    public function edit(DailySpecial $special): View
    {
        return view('admin.daily-special.form', compact('special'));
    }

    public function update(Request $request, DailySpecial $special): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->deleteImage($special->image_path);
            $data['image_path'] = $this->saveImage($request->file('image'));
        }

        if ($request->boolean('remove_image') && $special->image_path) {
            $this->deleteImage($special->image_path);
            $data['image_path'] = null;
        }

        $special->update($data);

        return redirect()->route('admin.daily-specials.index')
            ->with('success', 'Günlük özel başarıyla güncellendi.');
    }

    public function destroy(DailySpecial $special): RedirectResponse
    {
        $this->deleteImage($special->image_path);
        $special->delete();

        return redirect()->route('admin.daily-specials.index')
            ->with('success', 'Günlük özel silindi.');
    }

    private function specialsDir(): string
    {
        $root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
        $dir  = $root ? $root . DIRECTORY_SEPARATOR . 'daily-specials' : public_path('daily-specials');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir;
    }

    private function saveImage($file): string
    {
        $dir      = $this->specialsDir();
        $filename = uniqid('special_') . '.webp';
        $manager  = new ImageManager(new Driver());
        $manager->read($file)->cover(800, 600)->toWebp(85)->save($dir . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }

    private function deleteImage(?string $filename): void
    {
        if (!$filename) return;
        @unlink($this->specialsDir() . DIRECTORY_SEPARATOR . $filename);
    }

    private function rules(): array
    {
        return [
            'date'           => 'required|date',
            'title_tr'       => 'required|string|max:200',
            'title_en'       => 'nullable|string|max:200',
            'title_de'       => 'nullable|string|max:200',
            'description_tr' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_de' => 'nullable|string',
            'price'          => 'nullable|numeric|min:0',
            'is_active'      => 'nullable|boolean',
            'image'          => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ];
    }
}
