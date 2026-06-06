<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('analytics_events')->truncate();
        DB::table('analytics_pageviews')->truncate();
        DB::table('analytics_sessions')->truncate();
    }

    public function down(): void {}
};
