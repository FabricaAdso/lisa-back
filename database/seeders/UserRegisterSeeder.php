<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRegisterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRegisters = [
            [
                'identity_document' => '1061701851',
                'document_type_id' => 1,
                'name' => 'Alexander',
                'last_name' => 'Pardo',
                'email' => 'alexander.pardo@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701852',
                'document_type_id' => 1,
                'name' => 'Andrea',
                'last_name' => 'Gomez',
                'email' => 'andrea.gomez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701853',
                'document_type_id' => 1,
                'name' => 'Carlos',
                'last_name' => 'Lopez',
                'email' => 'carlos.lopez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701854',
                'document_type_id' => 1,
                'name' => 'Diana',
                'last_name' => 'Martinez',
                'email' => 'diana.martinez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701855',
                'document_type_id' => 1,
                'name' => 'Fernando',
                'last_name' => 'Perez',
                'email' => 'fernando.perez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701856',
                'document_type_id' => 1,
                'name' => 'Gabriela',
                'last_name' => 'Rodriguez',
                'email' => 'gabriela.rodriguez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701857',
                'document_type_id' => 1,
                'name' => 'Hector',
                'last_name' => 'Jimenez',
                'email' => 'hector.jimenez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701858',
                'document_type_id' => 1,
                'name' => 'Isabel',
                'last_name' => 'Garcia',
                'email' => 'isabel.garcia@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701859',
                'document_type_id' => 1,
                'name' => 'Jorge',
                'last_name' => 'Morales',
                'email' => 'jorge.morales@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701860',
                'document_type_id' => 1,
                'name' => 'Karen',
                'last_name' => 'Vargas',
                'email' => 'karen.vargas@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701861',
                'document_type_id' => 1,
                'name' => 'Laura',
                'last_name' => 'Salazar',
                'email' => 'laura.salazar@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701862',
                'document_type_id' => 1,
                'name' => 'Miguel',
                'last_name' => 'Castro',
                'email' => 'miguel.castro@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701863',
                'document_type_id' => 1,
                'name' => 'Nathalia',
                'last_name' => 'Cruz',
                'email' => 'nathalia.cruz@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701864',
                'document_type_id' => 1,
                'name' => 'Oscar',
                'last_name' => 'Rojas',
                'email' => 'oscar.rojas@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701865',
                'document_type_id' => 1,
                'name' => 'Paula',
                'last_name' => 'Sanchez',
                'email' => 'paula.sanchez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701866',
                'document_type_id' => 1,
                'name' => 'Ricardo',
                'last_name' => 'Ortiz',
                'email' => 'ricardo.ortiz@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701867',
                'document_type_id' => 1,
                'name' => 'Sofia',
                'last_name' => 'Navarro',
                'email' => 'sofia.navarro@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701868',
                'document_type_id' => 1,
                'name' => 'Tomás',
                'last_name' => 'Velasquez',
                'email' => 'tomas.velasquez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701869',
                'document_type_id' => 1,
                'name' => 'Valeria',
                'last_name' => 'Mejía',
                'email' => 'valeria.mejia@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701870',
                'document_type_id' => 1,
                'name' => 'Walter',
                'last_name' => 'Hernandez',
                'email' => 'walter.hernandez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701871',
                'document_type_id' => 1,
                'name' => 'Ximena',
                'last_name' => 'Rico',
                'email' => 'ximena.rico@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701872',
                'document_type_id' => 1,
                'name' => 'Yair',
                'last_name' => 'Gutierrez',
                'email' => 'yair.gutierrez@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701873',
                'document_type_id' => 1,
                'name' => 'Zaira',
                'last_name' => 'Córdoba',
                'email' => 'zaira.cordoba@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701874',
                'document_type_id' => 1,
                'name' => 'Adriana',
                'last_name' => 'Pineda',
                'email' => 'adriana.pineda@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701875',
                'document_type_id' => 1,
                'name' => 'Brian',
                'last_name' => 'Muñoz',
                'email' => 'brian.munoz@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701876',
                'document_type_id' => 1,
                'name' => 'Claudia',
                'last_name' => 'Rivera',
                'email' => 'claudia.rivera@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701877',
                'document_type_id' => 1,
                'name' => 'Daniel',
                'last_name' => 'Villalobos',
                'email' => 'daniel.villalobos@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701878',
                'document_type_id' => 1,
                'name' => 'Emilia',
                'last_name' => 'Torres',
                'email' => 'emilia.torres@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701879',
                'document_type_id' => 1,
                'name' => 'Fabian',
                'last_name' => 'Luna',
                'email' => 'fabian.luna@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061701880',
                'document_type_id' => 1,
                'name' => 'Gloria',
                'last_name' => 'Barreto',
                'email' => 'gloria.barreto@gmail.com',
                'password' => bcrypt('12345678'),
            ],
            [
                'identity_document' => '1061702745',
                'document_type_id' => 1,
                'name' => 'Yesith',
                'last_name' => 'Jimenez',
                'email' => 'deironyesithym21@gmail.com',
                'password' => bcrypt('12345678'),
            ],
        ];


        foreach ($userRegisters as $register) {
            $user = User::create($register);
            $user->trainingCenters()->attach(['training_center_id' => 39], ['role_id' => 1]);
        }
    }
}
