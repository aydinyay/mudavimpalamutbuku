<?php

namespace App\Modules\Vehicle\Services;

use App\Modules\Vehicle\Jobs\DeleteVehicleMediaFiles;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vehicle\Models\VehicleExpense;
use App\Modules\Vehicle\Models\VehicleIssue;
use App\Modules\Vehicle\Models\VehicleMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class VehicleMediaService
{
    public const DOCUMENT_ROLES = ['ruhsat', 'sigorta_police', 'muayene_belgesi', 'fatura'];

    public const PUBLIC_ROLES = ['genel', 'bolum', 'hasar_once', 'hasar_sonra'];

    public function store(Vehicle $vehicle, UploadedFile $file, string $role, ?VehicleExpense $expense = null, ?VehicleIssue $issue = null, ?string $caption = null): VehicleMedia
    {
        if ($expense && $issue) {
            throw ValidationException::withMessages(['media' => 'Bir medya en fazla bir kayda bağlanabilir.']);
        }
        if (($expense && $expense->vehicle_id !== $vehicle->id) || ($issue && $issue->vehicle_id !== $vehicle->id)) {
            throw ValidationException::withMessages(['media' => 'Medya yalnız aynı araca ait kayda bağlanabilir.']);
        }
        $mime = $file->getMimeType() ?: '';
        $photos = ['image/jpeg', 'image/png', 'image/webp'];
        $videos = ['video/mp4', 'video/webm', 'video/quicktime'];
        if (! in_array($mime, [...$photos, ...$videos], true)) {
            throw ValidationException::withMessages(['dosya' => 'Desteklenmeyen dosya türü.']);
        }
        $isVideo = in_array($mime, $videos, true);
        $max = $isVideo ? 100 * 1024 * 1024 : 15 * 1024 * 1024;
        if ($file->getSize() > $max) {
            throw ValidationException::withMessages(['dosya' => 'Dosya boyutu sınırı aşıldı.']);
        }
        $base = 'vehicles/'.$vehicle->id.'/'.bin2hex(random_bytes(20));
        if (! $isVideo && ! in_array($role, self::DOCUMENT_ROLES, true)) {
            $path = $base.'.webp';
            $image = (new ImageManager(new Driver))->read($file->getPathname());
            $image->scaleDown(width: 1600, height: 1200);
            Storage::disk('local')->put($path, (string) $image->toWebp(85));
            $mime = 'image/webp';
        } else {
            $ext = $file->guessExtension() ?: 'bin';
            $path = $file->storeAs(dirname($base), basename($base).'.'.$ext, 'local');
        }

        return $vehicle->media()->create(['vehicle_expense_id' => $expense?->id, 'vehicle_issue_id' => $issue?->id, 'tur' => $isVideo ? 'video' : 'foto', 'role' => $role, 'dosya_yolu' => $path, 'mime_type' => $mime, 'caption' => $caption]);
    }

    public function delete(VehicleMedia $media): void
    {
        DB::transaction(function () use ($media) {
            $path = $media->dosya_yolu;
            $media->delete();
            DeleteVehicleMediaFiles::dispatch([$path]);
        });
    }

    public function purgeParent(VehicleExpense|VehicleIssue $parent): void
    {
        if ($parent instanceof VehicleIssue && $parent->expenses()->withTrashed()->exists()) {
            throw ValidationException::withMessages(['issue' => 'Bağlı masrafı bulunan sorun kalıcı silinemez.']);
        }
        DB::transaction(function () use ($parent) {
            $paths = $parent->media()->pluck('dosya_yolu')->all();
            $parent->media()->delete();
            $parent->forceDelete();
            DeleteVehicleMediaFiles::dispatch($paths);
        });
    }
}
