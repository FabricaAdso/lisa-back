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
use Illuminate\Support\Facades\DB;
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
        $elements = request()->query('elements', 10);

        $sessions = Session::where('instructor_id', $instructor->id)->included()
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

    public function show($id)
    {
        $session = Session::included()->find($id);
        return response()->json($session);
    }

    public function indexLeader()
    {
        $user = User::find(Auth::id());
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return response()->json(['error' => 'El usuario no es un instructor'], 404);
        }

        $courseIds = Course::where('course_leader_id', $instructor->id)->pluck('id');
        if ($courseIds->isEmpty()) {
            return response()->json(['error' => 'El instructor no es líder de ningún curso'], 404);
        }
        $elements = request()->query('elements', 10);
        $filters = request('filter', []);
        $page = isset($filters['page']) ? $filters['page'] : 1;
        if (isset($filters['page'])) {
            unset($filters['page']);
        }
        if (isset($filters['elements'])) {
            unset($filters['elements']);
        }

        $sessions = Session::leaderFilter($filters, $courseIds)
            ->select('*', DB::raw("DATE_FORMAT(start_time, '%H:%i') as start_time"), DB::raw("DATE_FORMAT(end_time, '%H:%i') as end_time"))
            ->included()
            ->paginate(intval($elements), ['*'], 'page', $page);

        return response()->json($sessions);
    }


    public function filterOptions()
    {
        $user = User::find(Auth::id());
        $instructor = Instructor::where('user_id', $user->id)->first();
        if (!$instructor) {
            return response()->json(['error' => 'El usuario no es un instructor'], 404);
        }

        $courseIds = Course::where('course_leader_id', $instructor->id)->pluck('id');
        if ($courseIds->isEmpty()) {
            return response()->json(['error' => 'El instructor no es líder de ningún curso'], 404);
        }

        $sessions = Session::whereIn('course_id', $courseIds)
            ->with(['course', 'instructor.user', 'rap'])
            ->get();

        $courses = $sessions->pluck('course')->unique('id')->values();
        $instructors = $sessions->pluck('instructor')->unique('id')->values();
        $raps = $sessions->pluck('rap')->unique('id')->values();

        return response()->json([
            'courses' => $courses,
            'instructors' => $instructors,
            'raps' => $raps
        ]);
    }
}
