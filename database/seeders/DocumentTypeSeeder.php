<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Types = [
            ['name' => 'Cedula de Ciudadania', 'abbreviation' => 'C.C'],
            ['name' => 'Cedula de Extranjeria', 'abbreviation' => 'C.E'],
            ['name' => 'Tarjeta de Indentidad', 'abbreviation' => 'T.I'],
        ];

        foreach ($Types as $type) {
            DocumentType::create($type);
        }
    }
}
