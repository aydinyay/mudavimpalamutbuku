<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('restaurant_settings')->where('id', 1)->update([
            'name_tr'   => 'Müdavim Restaurant',
            'name_en'   => 'Müdavim Restaurant',
            'name_de'   => 'Müdavim Restaurant',
            'phone'     => '0554 442 77 48',
            'whatsapp'  => '+905544427748',
        ]);
    }

    public function down(): void
    {
        DB::table('restaurant_settings')->where('id', 1)->update([
            'name_tr'   => 'Müdavim Şef Restaurant',
            'name_en'   => 'Müdavim Şef Restaurant',
            'name_de'   => 'Müdavim Şef Restaurant',
            'phone'     => '0505 185 10 20',
            'whatsapp'  => '+905051851020',
        ]);
    }
};
