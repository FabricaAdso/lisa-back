<?php

namespace Tests\Feature\Events;

use App\Models\Instructor;
use Tests\TestCase;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class Prueba extends TestCase
{
    use RefreshDatabase; // Permite usar la base de datos en pruebas

    public function test_obtener_lista_de_sesiones()
    {
        // Crear usuario e instructor
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create(['user_id' => $user->id]);
    
        // Crear sesiones asociadas al instructor
        Session::factory()->count(5)->create(['instructor_id' => $instructor->id]);
    
        // Autenticar usuario antes de la solicitud
        $this->actingAs($user);
    
        // Hacer la solicitud
        $response = $this->get('/api/session');
    
        // Ver logs para verificar qué devuelve la API
        Log::info(json_encode($response->json(), JSON_PRETTY_PRINT));
    
        // Verificar respuesta
        $response->assertStatus(200)->assertJsonCount(5);
    }
}
