<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnowledgeNetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $networks = [
            'Tecnologías de la Información y las Comunicaciones (TIC)',
            'Contabilidad y Finanzas',
            'Diseño y Comunicación',
            'Salud y Bienestar',
            'Industria y Manufactura',
            'Agricultura y Agroindustria',
            'Comercio y Mercadeo',
            'Turismo y Gastronomía',
            'Energías Renovables y Sostenibilidad',
            'Construcción y Diseño de Infraestructura',
            'Automotriz',
            'Textiles y Confección',
            'Arte y Cultura',
            'Seguridad y Defensa',
            'Transporte y Logística',
        ];

        foreach ($networks as $network) {
            DB::table('knowledge_networks')->insert([
                'name' => $network,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
