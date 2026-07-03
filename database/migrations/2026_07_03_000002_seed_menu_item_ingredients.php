<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Tüm yemek kalemleri için içindekiler, et kökeni ve tahmini besin değerleri.
// Besin değerleri tipik Türk mutfağı porsiyonlarına göre hesaplanmış referans değerlerdir.
// Yasal yükümlülük (Ara. 2027) için diyetisyen onaylı analiz ile güncellenmelidir.
return new class extends Migration
{
    public function up(): void
    {
        $items = [
            // ── Atıştırmalıklar ──────────────────────────────────────────────
            'Patates Tava' => [
                'ingredients_tr' => 'patates, bitkisel kızartma yağı, tuz, karabiber',
                'ingredients_en' => 'potato, vegetable frying oil, salt, black pepper',
                'ingredients_de' => 'Kartoffeln, pflanzliches Frittieröl, Salz, schwarzer Pfeffer',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 480,
                'protein_g' => 5.0, 'fat_g' => 26.0, 'carbs_g' => 56.0,
            ],
            'Yoğurtlama' => [
                'ingredients_tr' => 'dana kıyma, yoğurt, tereyağı, sarımsak, pul biber, tuz, karabiber',
                'ingredients_en' => 'ground beef, yoghurt, butter, garlic, red pepper flakes, salt, black pepper',
                'ingredients_de' => 'Rinderhackfleisch, Joghurt, Butter, Knoblauch, Chiliflocken, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 180, 'calories' => 380,
                'protein_g' => 22.0, 'fat_g' => 20.0, 'carbs_g' => 20.0,
            ],
            'Sigara Böreği' => [
                'ingredients_tr' => 'yufka, beyaz peynir, yumurta, maydanoz, bitkisel kızartma yağı',
                'ingredients_en' => 'filo pastry, white cheese, egg, parsley, vegetable frying oil',
                'ingredients_de' => 'Filoteig, Weißkäse, Ei, Petersilie, pflanzliches Frittieröl',
                'meat_origin'    => null,
                'serving_size_g' => 120, 'calories' => 370,
                'protein_g' => 14.0, 'fat_g' => 20.0, 'carbs_g' => 34.0,
            ],

            // ── Salatalar ─────────────────────────────────────────────────────
            'Roka Salatası' => [
                'ingredients_tr' => 'roka, parmesan peyniri, zeytinyağı, limon, tuz, karabiber',
                'ingredients_en' => 'rocket, parmesan cheese, olive oil, lemon, salt, black pepper',
                'ingredients_de' => 'Rucola, Parmesan, Olivenöl, Zitrone, Salz, schwarzer Pfeffer',
                'meat_origin'    => null,
                'serving_size_g' => 150, 'calories' => 140,
                'protein_g' => 6.0, 'fat_g' => 10.0, 'carbs_g' => 6.0,
            ],
            'Çoban Salata' => [
                'ingredients_tr' => 'domates, salatalık, sivri biber, soğan, maydanoz, zeytinyağı, limon, tuz',
                'ingredients_en' => 'tomato, cucumber, green pepper, onion, parsley, olive oil, lemon, salt',
                'ingredients_de' => 'Tomate, Gurke, grüner Pfeffer, Zwiebel, Petersilie, Olivenöl, Zitrone, Salz',
                'meat_origin'    => null,
                'serving_size_g' => 220, 'calories' => 120,
                'protein_g' => 2.0, 'fat_g' => 8.0, 'carbs_g' => 10.0,
            ],
            'Domates Salatası' => [
                'ingredients_tr' => 'domates, soğan, maydanoz, zeytinyağı, limon, tuz, pul biber',
                'ingredients_en' => 'tomato, onion, parsley, olive oil, lemon, salt, red pepper flakes',
                'ingredients_de' => 'Tomate, Zwiebel, Petersilie, Olivenöl, Zitrone, Salz, Chiliflocken',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 110,
                'protein_g' => 2.0, 'fat_g' => 7.0, 'carbs_g' => 10.0,
            ],

            // ── Deniz Mezeleri ────────────────────────────────────────────────
            'Uskumru Çiroz' => [
                'ingredients_tr' => 'uskumru balığı (tütsülenmiş/kurutulmuş), zeytinyağı, limon, tuz',
                'ingredients_en' => 'smoked/dried mackerel, olive oil, lemon, salt',
                'ingredients_de' => 'geräucherte/getrocknete Makrele, Olivenöl, Zitrone, Salz',
                'meat_origin'    => 'Uskumru balığı',
                'serving_size_g' => 80, 'calories' => 210,
                'protein_g' => 20.0, 'fat_g' => 14.0, 'carbs_g' => 0.0,
            ],
            'Lakerda' => [
                'ingredients_tr' => 'palamut balığı (tuzlanmış/marine), zeytinyağı, limon',
                'ingredients_en' => 'cured bonito fish, olive oil, lemon',
                'ingredients_de' => 'gepökelter Bonito, Olivenöl, Zitrone',
                'meat_origin'    => 'Palamut balığı',
                'serving_size_g' => 80, 'calories' => 190,
                'protein_g' => 22.0, 'fat_g' => 10.0, 'carbs_g' => 0.0,
            ],
            'Karides Salata' => [
                'ingredients_tr' => 'karides, yeşil yapraklılar, zeytinyağı, limon, sarımsak, tuz, karabiber',
                'ingredients_en' => 'shrimp, mixed greens, olive oil, lemon, garlic, salt, black pepper',
                'ingredients_de' => 'Garnelen, gemischte Salatblätter, Olivenöl, Zitrone, Knoblauch, Salz, Pfeffer',
                'meat_origin'    => 'Karides',
                'serving_size_g' => 160, 'calories' => 180,
                'protein_g' => 22.0, 'fat_g' => 8.0, 'carbs_g' => 6.0,
            ],
            'Ahtapot Salatası' => [
                'ingredients_tr' => 'ahtapot, zeytinyağı, limon, sarımsak, kapari, maydanoz, tuz, karabiber',
                'ingredients_en' => 'octopus, olive oil, lemon, garlic, capers, parsley, salt, black pepper',
                'ingredients_de' => 'Oktopus, Olivenöl, Zitrone, Knoblauch, Kapern, Petersilie, Salz, Pfeffer',
                'meat_origin'    => 'Ahtapot',
                'serving_size_g' => 160, 'calories' => 175,
                'protein_g' => 20.0, 'fat_g' => 7.0, 'carbs_g' => 8.0,
            ],

            // ── Ara Sıcaklar ──────────────────────────────────────────────────
            'Kalamar Beyti' => [
                'ingredients_tr' => 'kalamar, lavaş ekmeği, domates sosu, tereyağı, pul biber, tuz, karabiber',
                'ingredients_en' => 'calamari, lavash bread, tomato sauce, butter, red pepper flakes, salt, black pepper',
                'ingredients_de' => 'Tintenfisch, Fladenbrot, Tomatensauce, Butter, Chiliflocken, Salz, Pfeffer',
                'meat_origin'    => 'Kalamar',
                'serving_size_g' => 200, 'calories' => 390,
                'protein_g' => 22.0, 'fat_g' => 18.0, 'carbs_g' => 34.0,
            ],
            'Karides Mantı' => [
                'ingredients_tr' => 'karides, buğday unu, yumurta, yoğurt, tereyağı, sarımsak, pul biber, tuz',
                'ingredients_en' => 'shrimp, wheat flour, egg, yoghurt, butter, garlic, red pepper flakes, salt',
                'ingredients_de' => 'Garnelen, Weizenmehl, Ei, Joghurt, Butter, Knoblauch, Chiliflocken, Salz',
                'meat_origin'    => 'Karides',
                'serving_size_g' => 220, 'calories' => 380,
                'protein_g' => 22.0, 'fat_g' => 14.0, 'carbs_g' => 42.0,
            ],
            'Deniz Mahsullü Börek' => [
                'ingredients_tr' => 'kalamar, karides, balık filetosu, yufka, yumurta, bitkisel kızartma yağı, tuz, karabiber',
                'ingredients_en' => 'calamari, shrimp, fish fillet, filo pastry, egg, vegetable oil, salt, black pepper',
                'ingredients_de' => 'Tintenfisch, Garnelen, Fischfilet, Filoteig, Ei, Pflanzenöl, Salz, Pfeffer',
                'meat_origin'    => 'Kalamar, karides, balık',
                'serving_size_g' => 180, 'calories' => 410,
                'protein_g' => 20.0, 'fat_g' => 22.0, 'carbs_g' => 36.0,
            ],
            'Çıtır Karides' => [
                'ingredients_tr' => 'karides, buğday unu, yumurta, galeta unu, bitkisel kızartma yağı, tuz',
                'ingredients_en' => 'shrimp, wheat flour, egg, breadcrumbs, vegetable frying oil, salt',
                'ingredients_de' => 'Garnelen, Weizenmehl, Ei, Semmelbrösel, Frittieröl, Salz',
                'meat_origin'    => 'Karides',
                'serving_size_g' => 150, 'calories' => 370,
                'protein_g' => 20.0, 'fat_g' => 18.0, 'carbs_g' => 34.0,
            ],
            'Kiremitte Mantar' => [
                'ingredients_tr' => 'mantar, tereyağı, sarımsak, maydanoz, tuz, karabiber',
                'ingredients_en' => 'mushrooms, butter, garlic, parsley, salt, black pepper',
                'ingredients_de' => 'Pilze, Butter, Knoblauch, Petersilie, Salz, schwarzer Pfeffer',
                'meat_origin'    => null,
                'serving_size_g' => 160, 'calories' => 130,
                'protein_g' => 4.0, 'fat_g' => 10.0, 'carbs_g' => 7.0,
            ],
            'Kiremitte Sıcak Ot' => [
                'ingredients_tr' => 'taze mevsim otları, zeytinyağı, sarımsak, tuz',
                'ingredients_en' => 'fresh seasonal herbs, olive oil, garlic, salt',
                'ingredients_de' => 'frische Saisonkräuter, Olivenöl, Knoblauch, Salz',
                'meat_origin'    => null,
                'serving_size_g' => 120, 'calories' => 90,
                'protein_g' => 2.0, 'fat_g' => 7.0, 'carbs_g' => 5.0,
            ],
            'Kiremitte Karides' => [
                'ingredients_tr' => 'karides, tereyağı, sarımsak, domates, pul biber, tuz, karabiber',
                'ingredients_en' => 'shrimp, butter, garlic, tomato, red pepper flakes, salt, black pepper',
                'ingredients_de' => 'Garnelen, Butter, Knoblauch, Tomate, Chiliflocken, Salz, Pfeffer',
                'meat_origin'    => 'Karides',
                'serving_size_g' => 180, 'calories' => 230,
                'protein_g' => 24.0, 'fat_g' => 12.0, 'carbs_g' => 6.0,
            ],
            'Kalamar Tava' => [
                'ingredients_tr' => 'kalamar, buğday unu, yumurta, bitkisel kızartma yağı, tuz, karabiber',
                'ingredients_en' => 'calamari, wheat flour, egg, vegetable frying oil, salt, black pepper',
                'ingredients_de' => 'Tintenfisch, Weizenmehl, Ei, Frittieröl, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Kalamar',
                'serving_size_g' => 180, 'calories' => 360,
                'protein_g' => 20.0, 'fat_g' => 16.0, 'carbs_g' => 32.0,
            ],
            'Levrek Simit' => [
                'ingredients_tr' => 'levrek balığı filetosu, simit (buğday unu, susam, tuz), yumurta, zeytinyağı, tuz, karabiber',
                'ingredients_en' => 'sea bass fillet, simit bread (wheat flour, sesame, salt), egg, olive oil, salt, black pepper',
                'ingredients_de' => 'Seebarschfilet, Simit-Brot (Weizenmehl, Sesam, Salz), Ei, Olivenöl, Salz, Pfeffer',
                'meat_origin'    => 'Levrek balığı',
                'serving_size_g' => 200, 'calories' => 370,
                'protein_g' => 30.0, 'fat_g' => 14.0, 'carbs_g' => 28.0,
            ],
            'Paçanga Böreği' => [
                'ingredients_tr' => 'pastırma (dana eti, çemen, tuz, baharatlar), biber, domates, yufka, yumurta, bitkisel kızartma yağı',
                'ingredients_en' => 'pastırma (beef, fenugreek paste, salt, spices), pepper, tomato, filo pastry, egg, frying oil',
                'ingredients_de' => 'Pastırma (Rindfleisch, Bockshornkleegewürz, Salz, Gewürze), Paprika, Tomate, Filoteig, Ei, Öl',
                'meat_origin'    => 'Dana (pastırma)',
                'serving_size_g' => 150, 'calories' => 430,
                'protein_g' => 18.0, 'fat_g' => 26.0, 'carbs_g' => 30.0,
            ],
            'Arnavut Ciğeri' => [
                'ingredients_tr' => 'dana/kuzu ciğeri, soğan, pul biber, zeytinyağı, maydanoz, tuz, karabiber',
                'ingredients_en' => 'beef/lamb liver, onion, red pepper flakes, olive oil, parsley, salt, black pepper',
                'ingredients_de' => 'Rind-/Lammsleber, Zwiebel, Chiliflocken, Olivenöl, Petersilie, Salz, Pfeffer',
                'meat_origin'    => 'Dana veya kuzu ciğeri',
                'serving_size_g' => 160, 'calories' => 290,
                'protein_g' => 26.0, 'fat_g' => 16.0, 'carbs_g' => 8.0,
            ],

            // ── Et Ürünleri ───────────────────────────────────────────────────
            'Izgara Köfte' => [
                'ingredients_tr' => 'dana kıyma, soğan, ekmek içi, tuz, karabiber, kimyon',
                'ingredients_en' => 'ground beef, onion, breadcrumb, salt, black pepper, cumin',
                'ingredients_de' => 'Rinderhackfleisch, Zwiebel, Semmelbrösel, Salz, schwarzer Pfeffer, Kreuzkümmel',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 200, 'calories' => 410,
                'protein_g' => 30.0, 'fat_g' => 26.0, 'carbs_g' => 10.0,
            ],
            'Kuzu Şiş' => [
                'ingredients_tr' => 'kuzu eti (but veya fileto), soğan, zeytinyağı, tuz, karabiber, kekik',
                'ingredients_en' => 'lamb (leg or fillet), onion, olive oil, salt, black pepper, thyme',
                'ingredients_de' => 'Lammfleisch (Keule oder Filet), Zwiebel, Olivenöl, Salz, Pfeffer, Thymian',
                'meat_origin'    => 'Kuzu',
                'serving_size_g' => 200, 'calories' => 390,
                'protein_g' => 32.0, 'fat_g' => 24.0, 'carbs_g' => 4.0,
            ],
            'Kuzu Pirzola' => [
                'ingredients_tr' => 'kuzu pirzola, sarımsak, zeytinyağı, biberiye, kekik, tuz, karabiber',
                'ingredients_en' => 'lamb chops, garlic, olive oil, rosemary, thyme, salt, black pepper',
                'ingredients_de' => 'Lammkoteletts, Knoblauch, Olivenöl, Rosmarin, Thymian, Salz, Pfeffer',
                'meat_origin'    => 'Kuzu',
                'serving_size_g' => 220, 'calories' => 500,
                'protein_g' => 30.0, 'fat_g' => 38.0, 'carbs_g' => 2.0,
            ],
            'Antrikot' => [
                'ingredients_tr' => 'dana antrikot, tereyağı, sarımsak, biberiye, tuz, karabiber',
                'ingredients_en' => 'beef entrecote, butter, garlic, rosemary, salt, black pepper',
                'ingredients_de' => 'Rindsentrecôte, Butter, Knoblauch, Rosmarin, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 250, 'calories' => 600,
                'protein_g' => 46.0, 'fat_g' => 44.0, 'carbs_g' => 2.0,
            ],
            'Et Bonfile' => [
                'ingredients_tr' => 'dana bonfile, tereyağı, tuz, karabiber',
                'ingredients_en' => 'beef tenderloin, butter, salt, black pepper',
                'ingredients_de' => 'Rinderfilet, Butter, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 200, 'calories' => 420,
                'protein_g' => 44.0, 'fat_g' => 26.0, 'carbs_g' => 1.0,
            ],
            'Lokum Bonfile' => [
                'ingredients_tr' => 'dana bonfile (lokum kesim), tereyağı, tuz, karabiber',
                'ingredients_en' => 'beef tenderloin medallions, butter, salt, black pepper',
                'ingredients_de' => 'Rinderfilet-Medaillons, Butter, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Dana',
                'serving_size_g' => 200, 'calories' => 440,
                'protein_g' => 44.0, 'fat_g' => 28.0, 'carbs_g' => 1.0,
            ],
            'Tavuk Şiş' => [
                'ingredients_tr' => 'tavuk göğsü, zeytinyağı, sarımsak, yoğurt, tuz, karabiber, pul biber',
                'ingredients_en' => 'chicken breast, olive oil, garlic, yoghurt, salt, black pepper, red pepper flakes',
                'ingredients_de' => 'Hähnchenbrust, Olivenöl, Knoblauch, Joghurt, Salz, Pfeffer, Chiliflocken',
                'meat_origin'    => 'Tavuk',
                'serving_size_g' => 200, 'calories' => 330,
                'protein_g' => 40.0, 'fat_g' => 14.0, 'carbs_g' => 6.0,
            ],

            // ── Deniz Ürünleri ────────────────────────────────────────────────
            'Levrek Izgara' => [
                'ingredients_tr' => 'levrek balığı, zeytinyağı, limon, tuz, karabiber',
                'ingredients_en' => 'sea bass, olive oil, lemon, salt, black pepper',
                'ingredients_de' => 'Seebarsch, Olivenöl, Zitrone, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Levrek balığı',
                'serving_size_g' => 300, 'calories' => 290,
                'protein_g' => 40.0, 'fat_g' => 14.0, 'carbs_g' => 0.0,
            ],
            'Çipura Izgara' => [
                'ingredients_tr' => 'çipura balığı, zeytinyağı, limon, tuz, karabiber',
                'ingredients_en' => 'sea bream, olive oil, lemon, salt, black pepper',
                'ingredients_de' => 'Dorade, Olivenöl, Zitrone, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Çipura balığı',
                'serving_size_g' => 300, 'calories' => 270,
                'protein_g' => 38.0, 'fat_g' => 12.0, 'carbs_g' => 0.0,
            ],
            'Tekir Tava' => [
                'ingredients_tr' => 'tekir balığı, buğday unu, zeytinyağı, tuz, karabiber, limon',
                'ingredients_en' => 'striped red mullet, wheat flour, olive oil, salt, black pepper, lemon',
                'ingredients_de' => 'Streifenbarbe, Weizenmehl, Olivenöl, Salz, Pfeffer, Zitrone',
                'meat_origin'    => 'Tekir balığı',
                'serving_size_g' => 200, 'calories' => 310,
                'protein_g' => 28.0, 'fat_g' => 14.0, 'carbs_g' => 12.0,
            ],
            'Izgara Ahtapot' => [
                'ingredients_tr' => 'ahtapot, nar ekşisi, zeytinyağı, sarımsak, limon, tuz, karabiber',
                'ingredients_en' => 'octopus, pomegranate molasses, olive oil, garlic, lemon, salt, black pepper',
                'ingredients_de' => 'Oktopus, Granatapfelmelasse, Olivenöl, Knoblauch, Zitrone, Salz, Pfeffer',
                'meat_origin'    => 'Ahtapot',
                'serving_size_g' => 200, 'calories' => 210,
                'protein_g' => 30.0, 'fat_g' => 6.0, 'carbs_g' => 8.0,
            ],
            'Güveç Ahtapot' => [
                'ingredients_tr' => 'ahtapot, domates, zeytinyağı, sarımsak, soğan, biber, tuz, baharatlar',
                'ingredients_en' => 'octopus, tomato, olive oil, garlic, onion, pepper, salt, spices',
                'ingredients_de' => 'Oktopus, Tomate, Olivenöl, Knoblauch, Zwiebel, Paprika, Salz, Gewürze',
                'meat_origin'    => 'Ahtapot',
                'serving_size_g' => 250, 'calories' => 230,
                'protein_g' => 26.0, 'fat_g' => 8.0, 'carbs_g' => 14.0,
            ],
            'Izgara Jumbo Karides' => [
                'ingredients_tr' => 'jumbo karides, zeytinyağı, sarımsak, limon, tuz, karabiber',
                'ingredients_en' => 'jumbo shrimp, olive oil, garlic, lemon, salt, black pepper',
                'ingredients_de' => 'Jumbo-Garnelen, Olivenöl, Knoblauch, Zitrone, Salz, schwarzer Pfeffer',
                'meat_origin'    => 'Jumbo karides',
                'serving_size_g' => 200, 'calories' => 190,
                'protein_g' => 30.0, 'fat_g' => 6.0, 'carbs_g' => 2.0,
            ],
            'Deniz Ürünleri Güveç' => [
                'ingredients_tr' => 'karides, ahtapot, kalamar, balık filetosu, domates, biber, zeytinyağı, sarımsak, tuz, baharatlar',
                'ingredients_en' => 'shrimp, octopus, calamari, fish fillet, tomato, pepper, olive oil, garlic, salt, spices',
                'ingredients_de' => 'Garnelen, Oktopus, Tintenfisch, Fischfilet, Tomate, Paprika, Olivenöl, Knoblauch, Salz',
                'meat_origin'    => 'Karides, ahtapot, kalamar, balık',
                'serving_size_g' => 300, 'calories' => 290,
                'protein_g' => 34.0, 'fat_g' => 10.0, 'carbs_g' => 16.0,
            ],

            // ── Tatlılar ──────────────────────────────────────────────────────
            'Katmer' => [
                'ingredients_tr' => 'yufka, antep fıstığı, kaymak, şeker, tereyağı',
                'ingredients_en' => 'filo pastry, pistachio, clotted cream, sugar, butter',
                'ingredients_de' => 'Filoteig, Pistazien, geronnene Sahne, Zucker, Butter',
                'meat_origin'    => null,
                'serving_size_g' => 150, 'calories' => 490,
                'protein_g' => 8.0, 'fat_g' => 28.0, 'carbs_g' => 54.0,
            ],
            'Kabak Tatlısı' => [
                'ingredients_tr' => 'balkabağı, şeker, ceviz, kaymak veya dondurma',
                'ingredients_en' => 'pumpkin, sugar, walnut, clotted cream or ice cream',
                'ingredients_de' => 'Kürbis, Zucker, Walnüsse, geronnene Sahne oder Eis',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 290,
                'protein_g' => 4.0, 'fat_g' => 8.0, 'carbs_g' => 50.0,
            ],

            // ── Soğuk İçecekler ───────────────────────────────────────────────
            'Gazlı İçecek' => [
                'ingredients_tr' => 'su, şeker, karbonik asit, gıda aroması, sitrik asit, gıda boyası',
                'ingredients_en' => 'water, sugar, carbonic acid, food flavouring, citric acid, food colouring',
                'ingredients_de' => 'Wasser, Zucker, Kohlensäure, Lebensmittelaroma, Zitronensäure, Lebensmittelfarbe',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 85,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 21.0,
            ],
            'Meyve Suyu' => [
                'ingredients_tr' => 'meyve suyu konsantresi, su, şeker, C vitamini',
                'ingredients_en' => 'fruit juice concentrate, water, sugar, vitamin C',
                'ingredients_de' => 'Fruchtsaftkonzentrat, Wasser, Zucker, Vitamin C',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 90,
                'protein_g' => 0.5, 'fat_g' => 0.0, 'carbs_g' => 22.0,
            ],
            'Soğuk Çay' => [
                'ingredients_tr' => 'su, çay ekstresi, şeker, sitrik asit, doğal aroma',
                'ingredients_en' => 'water, tea extract, sugar, citric acid, natural flavour',
                'ingredients_de' => 'Wasser, Teeextrakt, Zucker, Zitronensäure, natürliches Aroma',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 70,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 17.0,
            ],
            'Soda' => [
                'ingredients_tr' => 'su, karbonik asit, sodyum bikarbonat',
                'ingredients_en' => 'water, carbonic acid, sodium bicarbonate',
                'ingredients_de' => 'Wasser, Kohlensäure, Natriumbikarbonat',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 0,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Şalgam' => [
                'ingredients_tr' => 'şalgam, turp, su, tuz, limon, sarımsak, bulgur unu',
                'ingredients_en' => 'turnip, radish, water, salt, lemon, garlic, bulgur flour',
                'ingredients_de' => 'Steckrübe, Rettich, Wasser, Salz, Zitrone, Knoblauch, Bulgurmehl',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 20,
                'protein_g' => 0.5, 'fat_g' => 0.0, 'carbs_g' => 4.0,
            ],
            'Ayran' => [
                'ingredients_tr' => 'yoğurt, su, tuz',
                'ingredients_en' => 'yoghurt, water, salt',
                'ingredients_de' => 'Joghurt, Wasser, Salz',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 90,
                'protein_g' => 5.0, 'fat_g' => 4.0, 'carbs_g' => 8.0,
            ],
            'Su' => [
                'ingredients_tr' => 'içme suyu',
                'ingredients_en' => 'drinking water',
                'ingredients_de' => 'Trinkwasser',
                'meat_origin'    => null,
                'serving_size_g' => 330, 'calories' => 0,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],

            // ── Biralar ───────────────────────────────────────────────────────
            'Tuborg Malt' => [
                'ingredients_tr' => 'su, arpa maltı, şerbetçiotu, maya. Alkol içerir (%5).',
                'ingredients_en' => 'water, barley malt, hops, yeast. Contains alcohol (5%).',
                'ingredients_de' => 'Wasser, Gerstenmalz, Hopfen, Hefe. Enthält Alkohol (5%).',
                'meat_origin'    => null,
                'serving_size_g' => 330, 'calories' => 145,
                'protein_g' => 1.5, 'fat_g' => 0.0, 'carbs_g' => 12.0,
            ],
            'Tuborg Filtresiz' => [
                'ingredients_tr' => 'su, arpa maltı, şerbetçiotu, maya. Alkol içerir (%5).',
                'ingredients_en' => 'water, barley malt, hops, yeast. Contains alcohol (5%).',
                'ingredients_de' => 'Wasser, Gerstenmalz, Hopfen, Hefe. Enthält Alkohol (5%).',
                'meat_origin'    => null,
                'serving_size_g' => 330, 'calories' => 155,
                'protein_g' => 2.0, 'fat_g' => 0.0, 'carbs_g' => 14.0,
            ],
            'Carlsberg' => [
                'ingredients_tr' => 'su, arpa maltı, şerbetçiotu, maya. Alkol içerir (%5).',
                'ingredients_en' => 'water, barley malt, hops, yeast. Contains alcohol (5%).',
                'ingredients_de' => 'Wasser, Gerstenmalz, Hopfen, Hefe. Enthält Alkohol (5%).',
                'meat_origin'    => null,
                'serving_size_g' => 330, 'calories' => 142,
                'protein_g' => 1.0, 'fat_g' => 0.0, 'carbs_g' => 11.0,
            ],
            'Corona' => [
                'ingredients_tr' => 'su, arpa maltı, mısır, şerbetçiotu, maya. Alkol içerir (%4,6).',
                'ingredients_en' => 'water, barley malt, corn, hops, yeast. Contains alcohol (4.6%).',
                'ingredients_de' => 'Wasser, Gerstenmalz, Mais, Hopfen, Hefe. Enthält Alkohol (4,6%).',
                'meat_origin'    => null,
                'serving_size_g' => 330, 'calories' => 148,
                'protein_g' => 1.0, 'fat_g' => 0.0, 'carbs_g' => 14.0,
            ],

            // ── Rakı ──────────────────────────────────────────────────────────
            'Yeni Rakı Tek' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 40, 'calories' => 100,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Yeni Rakı Double' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 80, 'calories' => 200,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Yeni Rakı 20cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 500,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Yeni Rakı 35cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 350, 'calories' => 875,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Yeni Rakı 70cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 1750,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Efe Gold 20cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 500,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Efe Gold 35cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 350, 'calories' => 875,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Efe Gold 70cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 1750,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Beylerbeyi Göbek 20cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 500,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Beylerbeyi Göbek 35cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 350, 'calories' => 875,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Beylerbeyi Göbek 70cl' => [
                'ingredients_tr' => 'alkol, su, anis özü. Alkol içerir (%45).',
                'ingredients_en' => 'alcohol, water, anise extract. Contains alcohol (45%).',
                'ingredients_de' => 'Alkohol, Wasser, Anisextrakt. Enthält Alkohol (45%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 1750,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],

            // ── Şarap ─────────────────────────────────────────────────────────
            'Beyaz Şarap Kadeh' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%11-13).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (11-13%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (11-13%).',
                'meat_origin'    => null,
                'serving_size_g' => 150, 'calories' => 120,
                'protein_g' => 0.1, 'fat_g' => 0.0, 'carbs_g' => 4.0,
            ],
            'Angora Beyaz 70cl' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%12).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (12%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (12%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 560,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 19.0,
            ],
            'Kav Narince 70cl' => [
                'ingredients_tr' => 'Narince üzümü, maya, sülfür dioksit. Alkol içerir (%12,5).',
                'ingredients_en' => 'Narince grape, yeast, sulphur dioxide. Contains alcohol (12.5%).',
                'ingredients_de' => 'Narince-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (12,5%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 580,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 20.0,
            ],
            'Sarafin Sauvignon Blanc 70cl' => [
                'ingredients_tr' => 'Sauvignon Blanc üzümü, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'Sauvignon Blanc grape, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Sauvignon-Blanc-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 600,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 20.0,
            ],
            'Sarafin Chardonnay 70cl' => [
                'ingredients_tr' => 'Chardonnay üzümü, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'Chardonnay grape, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Chardonnay-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 600,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 18.0,
            ],
            'Kırmızı Şarap Kadeh' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%12-14).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (12-14%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (12-14%).',
                'meat_origin'    => null,
                'serving_size_g' => 150, 'calories' => 125,
                'protein_g' => 0.1, 'fat_g' => 0.0, 'carbs_g' => 4.0,
            ],
            'Angora Kırmızı 70cl' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 583,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 19.0,
            ],
            'Kav Öküzgözü 70cl' => [
                'ingredients_tr' => 'Öküzgözü üzümü, maya, sülfür dioksit. Alkol içerir (%13).',
                'ingredients_en' => 'Öküzgözü grape, yeast, sulphur dioxide. Contains alcohol (13%).',
                'ingredients_de' => 'Öküzgözü-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 590,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 20.0,
            ],
            'Sarafin Cabernet Sauvignon 70cl' => [
                'ingredients_tr' => 'Cabernet Sauvignon üzümü, maya, sülfür dioksit. Alkol içerir (%13,5).',
                'ingredients_en' => 'Cabernet Sauvignon grape, yeast, sulphur dioxide. Contains alcohol (13.5%).',
                'ingredients_de' => 'Cabernet-Sauvignon-Traube, Hefe, Schwefeldioxid. Enthält Alkohol (13,5%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 622,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 18.0,
            ],
            'Datça Wineyard Blush' => [
                'ingredients_tr' => 'üzüm, maya, sülfür dioksit. Alkol içerir (%12).',
                'ingredients_en' => 'grapes, yeast, sulphur dioxide. Contains alcohol (12%).',
                'ingredients_de' => 'Trauben, Hefe, Schwefeldioxid. Enthält Alkohol (12%).',
                'meat_origin'    => null,
                'serving_size_g' => 700, 'calories' => 560,
                'protein_g' => 0.7, 'fat_g' => 0.0, 'carbs_g' => 20.0,
            ],

            // ── İthal İçecekler ───────────────────────────────────────────────
            "Gordon's Gin" => [
                'ingredients_tr' => 'tahıl alkolü, su, ardıç meyvesi, kişniş, meyankökü ve botanikler. Alkol içerir (%37,5).',
                'ingredients_en' => 'grain alcohol, water, juniper berries, coriander, liquorice and botanicals. Contains alcohol (37.5%).',
                'ingredients_de' => 'Getreidespirituose, Wasser, Wacholderbeeren, Koriander, Lakritze und Botanicals. Enthält Alkohol (37,5%).',
                'meat_origin'    => null,
                'serving_size_g' => 40, 'calories' => 95,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Chivas Regal' => [
                'ingredients_tr' => 'İskoç viski (maltlanmış arpa, su, maya). Alkol içerir (%40).',
                'ingredients_en' => 'Scotch whisky (malted barley, water, yeast). Contains alcohol (40%).',
                'ingredients_de' => 'Scotch Whisky (Gerstenmalz, Wasser, Hefe). Enthält Alkohol (40%).',
                'meat_origin'    => null,
                'serving_size_g' => 40, 'calories' => 96,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Smirnoff Votka' => [
                'ingredients_tr' => 'tahıl alkolü, su. Alkol içerir (%37,5).',
                'ingredients_en' => 'grain alcohol, water. Contains alcohol (37.5%).',
                'ingredients_de' => 'Getreidespirituose, Wasser. Enthält Alkohol (37,5%).',
                'meat_origin'    => null,
                'serving_size_g' => 40, 'calories' => 90,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.0,
            ],
            'Aperol Spritz' => [
                'ingredients_tr' => 'Aperol (portakal kabuğu, rabarbaro, kinkin, şeker, alkol %11), prosecco, soda. Alkol içerir.',
                'ingredients_en' => 'Aperol (bitter orange, rhubarb, cinchona, sugar, alcohol 11%), prosecco, soda. Contains alcohol.',
                'ingredients_de' => 'Aperol (Bitterorange, Rhabarber, Chinin, Zucker, Alkohol 11%), Prosecco, Soda. Enthält Alkohol.',
                'meat_origin'    => null,
                'serving_size_g' => 300, 'calories' => 210,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 24.0,
            ],

            // ── Sıcak İçecekler ───────────────────────────────────────────────
            'Türk Kahvesi' => [
                'ingredients_tr' => 'Türk kahvesi (ince çekilmiş Arabica), su',
                'ingredients_en' => 'Turkish coffee (finely ground Arabica), water',
                'ingredients_de' => 'Türkischer Kaffee (fein gemahlener Arabica), Wasser',
                'meat_origin'    => null,
                'serving_size_g' => 90, 'calories' => 15,
                'protein_g' => 0.5, 'fat_g' => 0.0, 'carbs_g' => 3.0,
            ],
            'Americano' => [
                'ingredients_tr' => 'espresso kahvesi, su',
                'ingredients_en' => 'espresso coffee, water',
                'ingredients_de' => 'Espressokaffee, Wasser',
                'meat_origin'    => null,
                'serving_size_g' => 200, 'calories' => 10,
                'protein_g' => 0.5, 'fat_g' => 0.0, 'carbs_g' => 2.0,
            ],
            'Espresso' => [
                'ingredients_tr' => 'espresso kahvesi, su',
                'ingredients_en' => 'espresso coffee, water',
                'ingredients_de' => 'Espressokaffee, Wasser',
                'meat_origin'    => null,
                'serving_size_g' => 30, 'calories' => 5,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 1.0,
            ],
            'Çay' => [
                'ingredients_tr' => 'siyah çay, su',
                'ingredients_en' => 'black tea, water',
                'ingredients_de' => 'Schwarztee, Wasser',
                'meat_origin'    => null,
                'serving_size_g' => 100, 'calories' => 2,
                'protein_g' => 0.0, 'fat_g' => 0.0, 'carbs_g' => 0.5,
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
        DB::table('menu_items')->update([
            'ingredients_tr' => null,
            'ingredients_en' => null,
            'ingredients_de' => null,
            'meat_origin'    => null,
            'serving_size_g' => null,
            'calories'       => null,
            'protein_g'      => null,
            'fat_g'          => null,
            'carbs_g'        => null,
        ]);
    }
};
