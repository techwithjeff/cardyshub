<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Skincare', 'description' => 'Cleansers, moisturisers and daily treatments — the everyday essentials.'],
            ['name' => 'Serums', 'description' => 'Targeted, active-led treatments for dark spots, dullness, fine lines and more.'],
            ['name' => 'Facial Toners', 'description' => 'Alcohol-free toners that balance and prep skin for the rest of your routine.'],
            ['name' => 'Body Lotions', 'description' => 'Everyday hydration for the skin below your neck.'],
            ['name' => 'Bath & Body', 'description' => 'Shower gels, scrubs and body oils for a little extra ritual.'],
        ];

        foreach ($categories as $i => $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                ['name' => $data['name'], 'description' => $data['description'], 'sort_order' => $i]
            );
        }
    }
}
