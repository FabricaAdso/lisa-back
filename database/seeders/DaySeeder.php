<?php

namespace Database\Seeders;

use App\Models\Day;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days = [
            ['name' => 'Lunes', 'number' => 1],
            ['name' => 'Martes', 'number' => 2],
            ['name' => 'Miercoles', 'number' => 3],
            ['name' => 'Jueves', 'number' => 4],
            ['name' => 'Viernes', 'number' => 5],
            ['name' => 'Sabado', 'number' => 6],
            ['name' => 'Domingo', 'number' => 7],
        ];

        foreach ($days as $day){
            Day::create($day);
        }
    }
}
