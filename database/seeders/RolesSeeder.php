<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Usuario',
            'Coordinador academico',
            'Instructor',
            'Aprendiz',
            'Administrador',
        ];

        foreach ($roles as $role) {
            // Role::create(['name' => $role, 'guard_name' => 'web']);
            Role::create(['name' => $role, 'guard_name' => 'api']);
        }
    }
}
