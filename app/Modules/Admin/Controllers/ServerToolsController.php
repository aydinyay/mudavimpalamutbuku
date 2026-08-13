<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class ServerToolsController extends Controller
{
    public function index()
    {
        return view('admin.server-tools');
    }

    public function clearCache()
    {
        foreach (glob(base_path('bootstrap/cache/*.php')) ?: [] as $f) {
            @unlink($f);
        }
        foreach (glob(storage_path('framework/views/*.php')) ?: [] as $f) {
            @unlink($f);
        }
        if (function_exists('opcache_reset')) opcache_reset();

        return response()->json(['status' => 'ok', 'message' => 'Cache temizlendi.']);
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return response()->json(['status' => 'ok', 'output' => $output]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function testMail()
    {
        $to = request('email', config('mail.from.address'));
        try {
            \Illuminate\Support\Facades\Mail::raw(
                'Mudavim Palamutbükü — SMTP test maili. Gönderen: ' . config('mail.from.address'),
                fn ($m) => $m->to($to)->subject('SMTP Mail Testi — ' . now()->format('H:i:s'))
            );
            return response()->json(['status' => 'ok', 'message' => "$to adresine mail gönderildi."]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function sysInfo()
    {
        return response()->json([
            'php'        => PHP_VERSION,
            'laravel'    => app()->version(),
            'env'        => app()->environment(),
            'disk_free'  => round(disk_free_space('/') / 1073741824, 2) . ' GB',
            'disk_total' => round(disk_total_space('/') / 1073741824, 2) . ' GB',
            'memory'     => ini_get('memory_limit'),
        ]);
    }
}
