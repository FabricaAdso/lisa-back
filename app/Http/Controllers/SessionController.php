<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\Session;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{

    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function index()
    {
        $user = User::find(Auth::id());
        $instructor = Instructor::where('user_id', $user->id  )->first();
    
        if (!$instructor) {
            return response()->json([], 404); // Devuelve código 404 en lugar de una respuesta vacía
        }
    
        $sessions = Session::where('instructor_id', $instructor->id)->included()->filter()->get();
        
        Log::info("Sesiones encontradas: " . $sessions->count());
    
        return response()->json($sessions);
    }

    public function destroy($id)
    {
        $session =  Session::find($id);
        $session->assistances()->delete();
        $session->delete();
        return response()->json(['message' => 'Session eliminada exitosamente']);
    }

    // Crear sesión
    public function createSession(Request $request)
    {
        return $this->sessionService->createSession($request);
    }

    public function updateSessions(Request $request, ...$sessionIds)
    {
        return $this->sessionService->updateSessions($request, $sessionIds);
    }

}
