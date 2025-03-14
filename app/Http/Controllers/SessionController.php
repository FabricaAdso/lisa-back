<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Session;
use App\Models\User;
use App\Services\SessionService;
use Carbon\Carbon as CarbonCarbon;
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
            // Si no se encuentra un instructor, devolver un mensaje de error
            return response()->json(['error' => 'Instructor no encontrado'], 404);
        }
        $sessions = Session::where('instructor_id', $instructor->id)->included()->filter()->get();
        return response()->json($sessions);
    }

    public function getInassitanceInstructor()
    {
        $user = User::find(Auth::id());
        $elements = request()->query('elements', 15);
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], 404);
        }

        // Obtener todas las sesiones del instructor y agruparlas por mes y año
        $sessions = Session::where('instructor_id', $instructor->id)
            ->included()
            ->filter()
            ->paginate(intval($elements))
            ->groupBy(function ($session) {
                return Carbon::parse($session->date)->format('Y-m'); // Agrupar por año y mes
            });
        // Devolver las sesiones agrupadas por mes
        return response()->json($sessions);
    }


    public function sessionRap()
    {
        $sessions = Session::included()->filter()->get();
        return response()->json($sessions);
    }

    public function destroy($id)
    {
        $session =  Session::findOrFail($id);

        if ($session->date < Carbon::now()) {
            return response()->json(['message' => 'No se puede eliminar una sesión que ya ha pasado']);
        }
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

    public function show($id)
    {
        $session = Session::included()->find($id);
        return response()->json($session);
    }
}
