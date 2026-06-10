<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailySpecial;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

        $special->update($data);

        return redirect()->route('admin.daily-specials.index')
            ->with('success', 'Günlük özel başarıyla güncellendi.');
    }

    public function destroy(DailySpecial $special): RedirectResponse
    {
        $special->delete();

        return redirect()->route('admin.daily-specials.index')
            ->with('success', 'Günlük özel silindi.');
    }

    // ─── Validation ───────────────────────────────────────────

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
        ];
    }
}
