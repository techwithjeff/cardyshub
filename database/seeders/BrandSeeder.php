<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * The full A-Z range Cardyshub stocks.
     *
     * Descriptions are deliberately left null: these are real companies and
     * inventing marketing copy for them would put claims on the storefront that
     * nobody has approved. Fill `description` in from each brand's own
     * material before launch — the brand page hides the block while it is null.
     *
     * `is_featured` is not set here — ProductSeeder derives it from whether a
     * brand actually has published products, so the homepage rail can never
     * link through to an empty brand page.
     */
    public const BRANDS = [
        'Abib',
        'Acwell',
        'Adapalene',
        'Advanced Clinicals',
        'Advanced Korean',
        'Alada',
        'Alpha Arbutin',
        'Alpha Skincare',
        'Amlactin',
        'Anua',
        'APLB',
        'Aveeno',
        'Axis-Y',

        'Balance',
        'Beauty of Joseon',
        'Biore',
        'Black Girl Sunscreen',

        'C/M',
        'Clinitone',
        'Conscious Chemist',
        'Cosmo',
        'Cosrx',
        'Creighton',

        'Dr Meinaier',
        'Dr Rashel',
        'Dr Teals',
        'Dr. Althea',

        'EOS',
        'Estelin',
        'Ezanic',

        'Face Facts',

        'Garnier',
        'Glow influence',
        'Good Molecules',

        'Haruharu',
        'Hatomugi',
        'Hi gorgeous',

        'Illiyoon',
        'Isispharma',
        'Isntree',
        'Ivory glow',

        'Jumiso',

        'Kojisan',
        'Kojivit',
        'Kuu',

        'La Roche-Posay',

        'Mary & May',
        'Medicube',
        'Medix 5.5',
        'Missha',

        'Neutriherbs',
        'Neutrogena',
        'Nineless',
        'Niu Skin',
        'Nivea',
        'Numbuzin',

        'Olay',

        'Panoxyl',

        'RENOX',
        'Retin-A',

        'SEOUL 1988',
        'Sk',
        'Skeenpoint',
        'Skin Aqua',
        'Skin By Zaron',
        'SKIN1004',
        'SKIN1005',
        'Some By Mi',

        'The Ordinary',
        'Tiam',
        'Timeless',
        'Tonymoly',
        'Topicals',
        'Touch',
        'Tree Hut',

        'Vaseline',
        'Vc+',

        'YC',

        'Zapyt',
    ];

    public function run(): void
    {
        foreach (self::BRANDS as $name) {
            Brand::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        // The four invented brands the store shipped with are gone; drop them
        // and anything else no longer on the list, so the A-Z stays truthful.
        Brand::whereNotIn('slug', array_map(fn ($n) => Str::slug($n), self::BRANDS))
            ->each(function (Brand $brand) {
                $brand->products()->update(['brand_id' => null]);
                $brand->delete();
            });
    }
}
