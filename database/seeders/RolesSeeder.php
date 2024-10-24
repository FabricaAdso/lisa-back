<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Coordinador academico',
            'Instructor',
            'Aprendiz',
            'Administrador',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
            Role::create(['name' => $role, 'guard_name' => 'api']);
        }
    }
}
