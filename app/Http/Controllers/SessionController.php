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

class SessionController extends Controller
{

    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
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
