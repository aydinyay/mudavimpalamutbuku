<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // \u{00FC} = ü
        DB::table('restaurant_settings')->where('id', 1)->update([
            'name_tr'  => "M\u{00FC}davim Restaurant",
            'name_en'  => "M\u{00FC}davim Restaurant",
            'name_de'  => "M\u{00FC}davim Restaurant",
            'phone'    => '0554 442 77 48',
            'whatsapp' => '+905544427748',
        ]);
    }

    public function down(): void
    {
        DB::table('restaurant_settings')->where('id', 1)->update([
            'name_tr'  => "M\u{00FC}davim \u{015E}ef Restaurant",
            'name_en'  => "M\u{00FC}davim \u{015E}ef Restaurant",
            'name_de'  => "M\u{00FC}davim \u{015E}ef Restaurant",
            'phone'    => '0505 185 10 20',
            'whatsapp' => '+905051851020',
        ]);
    }
};
