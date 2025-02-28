<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Course;
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
        $leader = Course::where('course_leader_id', $user->id)->first();
        $instructor = Instructor::where('user_id', $leader->id)->first();
        if (!$instructor) {
            // Si no se encuentra un instructor, devolver un mensaje de error
            return response()->json();
        }
        $sessions = Session::where('instructor_id', $instructor->id)->included()->filter()->get();
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
