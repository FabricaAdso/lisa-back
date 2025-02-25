<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExcelImportSeeder extends Seeder
{
    public function run()
    {
        // Ruta al archivo SQL
        $path = database_path('seeders/Sql/ExcelImport.sql');

        // Leer el contenido del archivo SQL
        $sql = File::get($path);

        // Ejecutar el SQL para crear el procedimiento almacenado
        DB::unprepared($sql);

        $this->command->info('Procedimiento almacenado creado exitosamente.');
    }
}
