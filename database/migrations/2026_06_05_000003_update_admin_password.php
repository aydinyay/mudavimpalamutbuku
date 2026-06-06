<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@mudavim.com')
            ->update(['password' => Hash::make('HKaraoglu2026')]);
    }

    public function down(): void {}
};
