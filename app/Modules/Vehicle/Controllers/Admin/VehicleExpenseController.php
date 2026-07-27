<?php

namespace App\Modules\Vehicle\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vehicle\Models\VehicleChangeLog;
use App\Modules\Vehicle\Models\VehicleParty;
use App\Modules\Vehicle\Services\VehicleMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VehicleExpenseController extends Controller
{
    public function store(Request $request, Vehicle $vehicle)
    {
        DB::transaction(function () use ($request, $vehicle) {
            $data = $this->validated($request, $vehicle);
            $vehicle->expenses()->create($data);
        });

        return back()->with('success', 'Masraf eklendi.');
    }

    public function update(Request $request, Vehicle $vehicle, int $expense)
    {
        $expense = $vehicle->expenses()->findOrFail($expense);
        DB::transaction(function () use ($request, $vehicle, $expense) {
            $data = $this->validated($request, $vehicle);
            foreach ($data as $field => $new) {
                $old = $expense->getRawOriginal($field);
                $normalized = is_bool($new) ? (int) $new : $new;
                if ((string) $old !== (string) ($normalized ?? '')) {
                    VehicleChangeLog::create(['vehicle_id' => $vehicle->id, 'vehicle_expense_id' => $expense->id, 'alan_adi' => $field, 'eski_deger' => $old === null ? null : (string) $old, 'yeni_deger' => $new === null ? null : (string) $normalized]);
                }
            } $expense->update($data);
        });

        return back()->with('success', 'Masraf güncellendi ve değişiklikler kaydedildi.');
    }

    public function destroy(Vehicle $vehicle, int $expense)
    {
        $vehicle->expenses()->findOrFail($expense)->delete();

        return back()->with('success', 'Masraf arşivlendi; medyası korundu.');
    }

    public function forceDestroy(Vehicle $vehicle, int $expense, VehicleMediaService $service)
    {
        $expense = $vehicle->expenses()->withTrashed()->findOrFail($expense);
        $service->purgeParent($expense);

        return back()->with('success', 'Masraf ve bağlı medya kalıcı silindi.');
    }

    private function validated(Request $request, Vehicle $vehicle): array
    {
        $request->validate(['odeyen_yeni_ad' => 'nullable|string|max:150']);
        if ($request->filled('odeyen_yeni_ad')) {
            $request->merge(['odeyen_party_id' => VehicleParty::create(['ad' => $request->string('odeyen_yeni_ad')->toString()])->id]);
        }
        $data = $request->validate(['tarih' => 'required|date', 'aciklama' => 'required|string|max:255', 'tutar' => 'required|numeric|min:0|max:9999999999.99', 'kategori' => ['required', Rule::in(['yakit', 'bakim', 'yag_degisimi', 'lastik', 'sigorta', 'vergi', 'parca', 'iscilik', 'diger'])], 'odeyen_party_id' => 'required|exists:vehicle_parties,id', 'borclu_party_id' => 'nullable|exists:vehicle_parties,id', 'mahsup_durumu' => ['required', Rule::in(['borc_yok', 'alinacak', 'kapandi'])], 'settled_at' => 'nullable|date', 'vehicle_issue_id' => ['nullable', 'integer', function ($a, $v, $fail) use ($vehicle) {
            if (! $vehicle->issues()->whereKey($v)->exists()) {
                $fail('Seçilen sorun bu araca ait değil.');
            }
        }], 'km' => 'nullable|integer|min:0', 'parca_markasi' => 'nullable|string|max:100', 'uretim_yili' => 'nullable|integer|min:1900|max:'.(now()->year + 1), 'nereden_alindi' => 'nullable|string|max:200', 'garanti_bitis_tarihi' => 'nullable|date', 'vitrinde_goster' => 'sometimes|boolean', 'public_summary' => 'nullable|string|max:240', 'notlar' => 'nullable|string']);
        $status = $data['mahsup_durumu'];
        if ($status === 'borc_yok' && (! empty($data['borclu_party_id']) || ! empty($data['settled_at']))) {
            $this->matrixError();
        } if ($status === 'alinacak' && (empty($data['borclu_party_id']) || ! empty($data['settled_at']))) {
            $this->matrixError();
        } if ($status === 'kapandi' && (empty($data['borclu_party_id']) || empty($data['settled_at']))) {
            $this->matrixError();
        }
        $data['vitrinde_goster'] = $request->boolean('vitrinde_goster');
        if ($data['vitrinde_goster'] && blank($data['public_summary'] ?? null)) {
            throw ValidationException::withMessages(['public_summary' => 'Vitrinde gösterilen kayıt için kısa özet zorunludur.']);
        }

        return $data;
    }

    private function matrixError(): never
    {
        throw ValidationException::withMessages(['mahsup_durumu' => 'Mahsup durumu, borçlu taraf ve kapanış tarihi birbiriyle uyumlu değil.']);
    }
}
