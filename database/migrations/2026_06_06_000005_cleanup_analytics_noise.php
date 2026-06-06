<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Spotify polling, statik dosya ve bot isteklerini sil
        $noisePatterns = [
            '/spotify/status',
            '/images/%',
            '/favicon%',
            '/build/%',
            '/wp-login.php',
            '/wp-admin%',
            '/xmlrpc.php',
            '//%',
        ];

        foreach ($noisePatterns as $pattern) {
            DB::table('analytics_pageviews')->where('page_path', 'like', $pattern)->delete();
        }

        // Yalnızca gürültülü sayfalara ait olan oturumları temizle
        // (landing_page'i gürültülü olan ve hiç gerçek sayfa görüntülemesi olmayan sessionlar)
        DB::statement("
            DELETE FROM analytics_sessions
            WHERE session_id NOT IN (
                SELECT DISTINCT session_id FROM analytics_pageviews
            )
        ");
    }

    public function down(): void {}
};
