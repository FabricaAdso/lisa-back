<?php

namespace Database\Seeders;

use App\Models\Day;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolesSeeder::class,
            DocumentTypeSeeder::class,
            RegionalSeeder::class,
            TrainingCenterSeeder::class,
            UserRegisterSeeder::class
            // DaySeeder::class,
        ]);
        // $this->call(DepartamentSeeder::class);
        // $this->call(MunicipalitySeeder::class);
    }
}
