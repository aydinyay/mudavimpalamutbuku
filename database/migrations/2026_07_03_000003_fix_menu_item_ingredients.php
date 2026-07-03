<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Önceki migration'da isim uyuşmazlığı nedeniyle atlanmış öğelerin düzeltmesi.
// Gerçek TR isimleri QR menüden okunarak alındı.
return new class extends Migration
{
    public function up(): void
    {
        $items = [
            // ── Et Ürünleri — gerçek gramaj isimlerle ─────────────────────────
            'Izgara Köfte (170 Gr)' => [
                'ingredients_tr' => 'dana kıyma, soğan, ekmek içi, tuz, karabiber, kimyon',
                'ingredients_en' => 'ground beef, onion, breadcrumb, salt, black pepper, cumin',
                'ingredients_de' => 'Rinderhackfleisch, Zwiebel, Semmelbrösel, Salz, schwarzer Pfeffer, Kreuzkümmel',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 170, 'calories' => 350,
                'protein_g' => 26.0, 'fat_g' => 22.0, 'carbs_g' => 8.0,
            ],
            'Kuzu Şiş (170 Gr)' => [
                'ingredients_tr' => 'kuzu eti (but veya fileto), soğan, zeytinyağı, tuz, karabiber, kekik',
                'ingredients_en' => 'lamb (leg or fillet), onion, olive oil, salt, black pepper, thyme',
                'ingredients_de' => 'Lammfleisch (Keule oder Filet), Zwiebel, Olivenöl, Salz, Pfeffer, Thymian',
                'meat_origin'    => 'Kuzu',
                'serving_size_g' => 170, 'calories' => 330,
                'protein_g' => 27.0, 'fat_g' => 20.0, 'carbs_g' => 3.0,
            ],
            'Kuzu Pirzola (170 Gr)' => [
                'ingredients_tr' => 'kuzu pirzola, sarımsak, zeytinyağı, biberiye, kekik, tuz, karabiber',
                'ingredients_en' => 'lamb chops, garlic, olive oil, rosemary, thyme, salt, black pepper',
                'ingredients_de' => 'Lammkoteletts, Knoblauch, Olivenöl, Rosmarin, Thymian, Salz, Pfeffer',
                'meat_origin'    => 'Kuzu',
                'serving_size_g' => 170, 'calories' => 390,
                'protein_g' => 23.0, 'fat_g' => 30.0, 'carbs_g' => 2.0,
            ],
            'Antrikot (200 Gr)' => [
                'ingredients_tr' => 'dana antrikot, tereyağı, sarımsak, biberiye, tuz, karabiber',
                'ingredients_en' => 'beef entrecote, butter, garlic, rosemary, salt, black pepper',
                'ingredients_de' => 'Rindsentrecôte, Butter, Knoblauch, Rosmarin, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 200, 'calories' => 480,
                'protein_g' => 37.0, 'fat_g' => 35.0, 'carbs_g' => 2.0,
            ],
            'Et Bonfile (180 Gr)' => [
                'ingredients_tr' => 'dana bonfile, tereyağı, tuz, karabiber',
                'ingredients_en' => 'beef tenderloin, butter, salt, black pepper',
                'ingredients_de' => 'Rinderfilet, Butter, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 180, 'calories' => 380,
                'protein_g' => 40.0, 'fat_g' => 23.0, 'carbs_g' => 1.0,
            ],
            'Lokum Bonfile (180 Gr)' => [
                'ingredients_tr' => 'dana bonfile (lokum kesim), tereyağı, tuz, karabiber',
                'ingredients_en' => 'beef tenderloin medallions, butter, salt, black pepper',
                'ingredients_de' => 'Rinderfilet-Medaillons, Butter, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 180, 'calories' => 400,
                'protein_g' => 40.0, 'fat_g' => 25.0, 'carbs_g' => 1.0,
            ],
            'Tavuk Şiş (250 Gr)' => [
                'ingredients_tr' => 'tavuk göğsü, zeytinyağı, sarımsak, yoğurt, tuz, karabiber, pul biber',
                'ingredients_en' => 'chicken breast, olive oil, garlic, yoghurt, salt, black pepper, red pepper flakes',
                'ingredients_de' => 'Hähnchenbrust, Olivenöl, Knoblauch, Joghurt, Salz, Pfeffer, Chiliflocken',
                'meat_origin'    => 'Tavuk',
                'serving_size_g' => 250, 'calories' => 415,
                'protein_g' => 50.0, 'fat_g' => 18.0, 'carbs_g' => 8.0,
            ],

            // ── Deniz Ürünleri — gerçek gramaj isimlerle ──────────────────────
            'Levrek Izgara (400 Gr)' => [
                'ingredients_tr' => 'levrek balığı, zeytinyağı, limon, tuz, karabiber',
                'ingredients_en' => 'sea bass, olive oil, lemon, salt, black pepper',
                'ingredients_de' => 'Seebarsch, Olivenöl, Zitrone, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Levrek balığı',
                'serving_size_g' => 400, 'calories' => 390,
                'protein_g' => 53.0, 'fat_g' => 19.0, 'carbs_g' => 0.0,
            ],
            'Çipura Izgara (400 Gr)' => [
                'ingredients_tr' => 'çipura balığı, zeytinyağı, limon, tuz, karabiber',
                'ingredients_en' => 'sea bream, olive oil, lemon, salt, black pepper',
                'ingredients_de' => 'Dorade, Olivenöl, Zitrone, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Çipura balığı',
                'serving_size_g' => 400, 'calories' => 360,
                'protein_g' => 51.0, 'fat_g' => 16.0, 'carbs_g' => 0.0,
            ],
            'Tekir Tava (300 Gr)' => [
                'ingredients_tr' => 'tekir balığı, buğday unu, zeytinyağı, tuz, karabiber, limon',
                'ingredients_en' => 'striped red mullet, wheat flour, olive oil, salt, black pepper, lemon',
                'ingredients_de' => 'Streifenbarbe, Weizenmehl, Olivenöl, Salz, Pfeffer, Zitrone',
                'meat_origin'    => 'Tekir balığı',
                'serving_size_g' => 300, 'calories' => 465,
                'protein_g' => 42.0, 'fat_g' => 21.0, 'carbs_g' => 18.0,
            ],
            'Izgara Jumbo Karides 3 Ad.' => [
                'ingredients_tr' => 'jumbo karides, zeytinyağı, sarımsak, limon, tuz, karabiber',
                'ingredients_en' => 'jumbo shrimp (3 pcs), olive oil, garlic, lemon, salt, black pepper',
                'ingredients_de' => 'Jumbo-Garnelen (3 Stk.), Olivenöl, Knoblauch, Zitrone, Salz, Pfeffer',
                'meat_origin'    => 'Jumbo karides',
                'serving_size_g' => 200, 'calories' => 190,
                'protein_g' => 30.0, 'fat_g' => 6.0, 'carbs_g' => 2.0,
            ],

            // ── Ara Sıcaklar ──────────────────────────────────────────────────
            'Çıtır Karides 1 Adet' => [
                'ingredients_tr' => 'karides, buğday unu, yumurta, galeta unu, bitkisel kızartma yağı, tuz',
                'ingredients_en' => 'shrimp, wheat flour, egg, breadcrumbs, vegetable frying oil, salt',
                'ingredients_de' => 'Garnelen, Weizenmehl, Ei, Semmelbrösel, Frittieröl, Salz',
                'meat_origin'    => 'Karides',
                'serving_size_g' => 150, 'calories' => 370,
                'protein_g' => 20.0, 'fat_g' => 18.0, 'carbs_g' => 34.0,
            ],

            // ── Soğuk İçecekler — gerçek isimler (çoğul/farklı) ──────────────
            'Gazlı İçecekler' => [
                'ingredients_tr' => 'su, şeker, karbonik asit, gıda aroması, sitrik asit, gıda boyası',
                'ingredients_en' => 'water, sugar, carbonic acid, food flavouring, citric acid, food colouring',
                'ingredients_de' => 'Wasser, Zucker, Kohlensäure, Lebensmittelaroma, Zitronensäure, Lebensmittelfarbe',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 85,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 21.0,
            ],
            'Meyve Suları' => [
                'ingredients_tr' => 'meyve suyu konsantresi, su, şeker, C vitamini',
                'ingredients_en' => 'fruit juice concentrate, water, sugar, vitamin C',
                'ingredients_de' => 'Fruchtsaftkonzentrat, Wasser, Zucker, Vitamin C',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 90,
                'protein_g' => 0.5, 'fat_g' => 0.0, 'carbs_g' => 22.0,
            ],
            'Soğuk Çaylar' => [
                'ingredients_tr' => 'su, çay ekstresi, şeker, sitrik asit, doğal aroma',
                'ingredients_en' => 'water, tea extract, sugar, citric acid, natural flavour',
                'ingredients_de' => 'Wasser, Teeextrakt, Zucker, Zitronensäure, natürliches Aroma',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 70,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 17.0,
            ],
            'Şalgam Suyu' => [
                'ingredients_tr' => 'şalgam, turp, su, tuz, limon, sarımsak, bulgur unu',
                'ingredients_en' => 'turnip, radish, water, salt, lemon, garlic, bulgur flour',
                'ingredients_de' => 'Steckrübe, Rettich, Wasser, Salz, Zitrone, Knoblauch, Bulgurmehl',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 20,
                'protein_g' => 0.5, 'fat_g' => 0.0, 'carbs_g' => 4.0,
            ],
            'Su (1 Lt)' => [
                'ingredients_tr' => 'içme suyu',
                'ingredients_en' => 'drinking water',
                'ingredients_de' => 'Trinkwasser',
                'meat_origin'    => null,
                'serving_size_g' => 1000, 'calories' => 0,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],

            // ── Rakı — gerçek isimler ─────────────────────────────────────────
            'Tek Rakı (Yeni Rakı)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 40, 'calories' => 100,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Double Rakı (Yeni Rakı)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 80, 'calories' => 200,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Yeni Rakı (20 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 500,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Yeni Rakı (35 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 350, 'calories' => 875,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Yeni Rakı (70 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 1750,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Efe Gold (20 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 500,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Efe Gold (35 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 350, 'calories' => 875,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Efe Gold (70 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 1750,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Beylerbeyi Göbek (20 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 500,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Beylerbeyi Göbek (35 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 350, 'calories' => 875,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Beylerbeyi Göbek (70 Cl)' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 1750,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],

            // ── Şarap — gerçek isimler ────────────────────────────────────────
            'Kadeh Beyaz Şarap' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%11-13).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (11-13%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (11-13%).',
                'meat_origin'    => null,
                'serving_size_g' => 150, 'calories' => 120,
                'protein_g' => 0.1, 'fat_g' => 0.0, 'carbs_g' => 4.0,
            ],
            'Angora Beyaz (70 Cl)' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%12).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (12%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (12%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 560,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 19.0,
            ],
            'Kav Narince (70 Cl)' => [
                'ingredients_tr' => 'Narince üzümü, maya, sülfür dioksit. Alkol içerir (%12,5).',
                'ingredients_en' => 'Narince grape, yeast, sulphur dioxide. Contains alcohol (12.5%).',
                'ingredients_de' => 'Narince-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (12,5%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 580,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 20.0,
            ],
            'Sarafin Sauvignon Blanc (70 Cl)' => [
                'ingredients_tr' => 'Sauvignon Blanc üzümü, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'Sauvignon Blanc grape, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Sauvignon-Blanc-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 600,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 20.0,
            ],
            'Sarafin Chardonnay (70 Cl)' => [
                'ingredients_tr' => 'Chardonnay üzümü, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'Chardonnay grape, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Chardonnay-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 600,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 18.0,
            ],
            'Kadeh Kırmızı Şarap' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%12-14).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (12-14%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (12-14%).',
                'meat_origin'    => null,
                'serving_size_g' => 150, 'calories' => 125,
                'protein_g' => 0.1, 'fat_g' => 0.0, 'carbs_g' => 4.0,
            ],
            'Angora Kırmızı (70 Cl)' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 583,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 19.0,
            ],
            'Kav Öküzgözü (70 Cl)' => [
                'ingredients_tr' => 'Öküzgözü üzümü, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'Öküzgözü grape, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Öküzgözü-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 590,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 20.0,
            ],
            'Sarafin Cabernet Sauvignon (70 Cl)' => [
                'ingredients_tr' => 'Cabernet Sauvignon üzümü, maya, sülfür dioksit. Alkol içerir (%13,5).',
                'ingredients_en' => 'Cabernet Sauvignon grape, yeast, sulphur dioxide. Contains alcohol (13.5%).',
                'ingredients_de' => 'Cabernet-Sauvignon-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13,5%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 622,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 18.0,
            ],

            // ── İthal İçecekler — gerçek isimler ─────────────────────────────
            "Gordon's Cin" => [
                'ingredients_tr' => 'tahıl alkolü, su, ardıç meyvesi, kişniş, meyankökü ve botanikler. Alkol içerir (%37,5).',
                'ingredients_en' => 'grain alcohol, water, juniper berries, coriander, liquorice and botanicals. Contains alcohol (37.5%).',
                'ingredients_de' => 'Getreidespirituose, Wasser, Wacholderbeeren, Koriander, Lakritze und Botanicals. Enthält Alkohol (37,5%).',
                'meat_origin'    => null,
                'serving_size_g' => 40, 'calories' => 95,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Chivas Regal 12 Yrs' => [
                'ingredients_tr' => 'İskoç viski (maltlanmış arpa, su, maya). Alkol içerir (%40).',
                'ingredients_en' => 'Scotch whisky (malted barley, water, yeast). Contains alcohol (40%).',
                'ingredients_de' => 'Scotch Whisky (Gerstenmalz, Wasser, Hefe). Enthält Alkohol (40%).',
                'meat_origin'    => null,
                'serving_size_g' => 40, 'calories' => 96,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
        ];

        foreach ($items as $name => $data) {
            $translation = DB::table('menu_item_translations')
                ->where('locale', 'tr')
                ->where('name', $name)
                ->first();

            if ($translation) {
                DB::table('menu_items')
                    ->where('id', $translation->menu_item_id)
                    ->update($data);
            }
        }
    }

    public function down(): void
    {
        // Önceki migration değerleri geri yüklenmez — tüm alanları NULL'a çek
        $names = [
            'Izgara Köfte (170 Gr)', 'Kuzu Şiş (170 Gr)', 'Kuzu Pirzola (170 Gr)',
            'Antrikot (200 Gr)', 'Et Bonfile (180 Gr)', 'Lokum Bonfile (180 Gr)', 'Tavuk Şiş (250 Gr)',
            'Levrek Izgara (400 Gr)', 'Çipura Izgara (400 Gr)', 'Tekir Tava (300 Gr)',
            'Izgara Jumbo Karides 3 Ad.', 'Çıtır Karides 1 Adet',
        ];

        foreach ($names as $name) {
            $translation = DB::table('menu_item_translations')
                ->where('locale', 'tr')->where('name', $name)->first();
            if ($translation) {
                DB::table('menu_items')->where('id', $translation->menu_item_id)
                    ->update(['ingredients_tr' => null, 'ingredients_en' => null,
                              'ingredients_de' => null, 'meat_origin' => null,
                              'serving_size_g' => null, 'calories' => null,
                              'protein_g' => null, 'fat_g' => null, 'carbs_g' => null]);
            }
        }
    }
};
