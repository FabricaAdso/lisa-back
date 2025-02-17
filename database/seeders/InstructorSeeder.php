<?php

namespace Database\Seeders;
use App\Models\Instructor;
use App\Models\User;
use App\Models\TrainingCenter; // Si necesitas asociar un centro de formación
use App\Models\KnowledgeNetwork; // Si necesitas asociar una red de conocimiento
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    public function run()
    {
        // Obtener el usuario con ID 30
        $user = User::find(30);

        // Verificar si el usuario existe
        if (!$user) {
            $this->command->error('Usuario con ID 30 no encontrado.');
            return;
        }

        // Opcional: Obtener un TrainingCenter y KnowledgeNetwork si lo necesitas
        $trainingCenter = TrainingCenter::first(); // Si quieres asignar el primer centro de formación
        $knowledgeNetwork = KnowledgeNetwork::first(); // Si quieres asignar la primera red de conocimiento

        // Crear el instructor con el usuario 30
        Instructor::create([
            'user_id' => $user->id, // Asignamos el usuario 30
            'state' => 'Activo', // Estado activo por defecto
            'training_center_id' => $trainingCenter ? $trainingCenter->id : null, // Asociamos el centro de formación, si existe
            'knowledge_network_id' => $knowledgeNetwork ? $knowledgeNetwork->id : null, // Asociamos la red de conocimiento, si existe
        ]);
    }
}
