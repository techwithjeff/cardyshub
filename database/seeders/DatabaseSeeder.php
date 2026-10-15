<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            // Brands first: products now reference them by slug rather than
            // being distributed round-robin after the fact.
            BrandSeeder::class,
            ProductSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
