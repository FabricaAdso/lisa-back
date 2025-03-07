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
use Illuminate\Support\Carbon;
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
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return response()->json(['error' => 'El usuario no es un instructor'], 404);
        }

        // Verificar cursos en los que el instructor es líder
        $courseIds = Course::where('course_leader_id', $instructor->id)->pluck('id');
        if ($courseIds->isEmpty()) {
            return response()->json(['error' => 'El instructor no es líder de ningún curso'], 404);
        }

        // Tomar 'elements' de la query, por defecto 10

        $elements = request()->query('elements', 10);

        // Aplicar included(), filter() y paginar
        $sessions = Session::whereHas('course', function ($query) use ($instructor) {
            $query->where('course_leader_id', $instructor->id);
        })
        ->included()
        ->filter()
        ->paginate(intval($elements));

        // Log::info(json_encode($sessions, JSON_PRETTY_PRINT));

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

    public function show($id) {
        $session = Session::included()->find($id);
        return response()->json($session);
    }

}
