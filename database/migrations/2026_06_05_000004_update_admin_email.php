<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@mudavim.com')
            ->update(['email' => 'admin@mudavimpalamutbuku.com']);
    }

    public function down(): void {}
};
