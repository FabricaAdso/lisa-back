<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DepartamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Ruta del archivo SQL
        $path = database_path('seeders/Sql/Departaments.sql');
        
        // Leer el contenido del archivo SQL
        $sql = File::get($path);
        
        // Ejecutar el SQL sin preparación
        DB::unprepared($sql);

        // Mensaje opcional para verificar que se ejecutó correctamente
        $this->command->info('Departamentos insertados desde el archivo SQL.');
    }
}
