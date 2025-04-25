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
            ->get();
        // Log::info(json_encode($sessions, JSON_PRETTY_PRINT));

        return response()->json($sessions);
    }

    public function getSessionsMount()
    {
        $user = User::find(Auth::id());
        $monthFilter = request()->query('month'); // Formato: "YYYY-MM"
        $yearFilter = request()->query('year');   // Filtro adicional por año

        $instructor = Instructor::where('user_id', $user->id)->first();
        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], 404);
        }

        // Consulta base para las sesiones del instructor
        $query = Session::with(['course.environment.headquarters'])
            ->where('instructor_id', $instructor->id)
            ->filter();

        // Aplicar filtros si existen
        if ($monthFilter) {
            $query->whereYear('date', substr($monthFilter, 0, 4))
                ->whereMonth('date', substr($monthFilter, 5, 2));
        } elseif ($yearFilter) {
            $query->whereYear('date', $yearFilter);
        }

        // Paginar directamente la consulta (más eficiente que obtener todos los registros)
        $paginatedSessions = $query->orderBy('date')->paginate(1000);

        // nombre de la sede
        $transformed = $paginatedSessions->getCollection()->map(function ($session) {
            return [
                'id' => $session->id,
                'date' => $session->date,
                'end_date' => $session->end_date,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'instructor_id' => $session->instructor_id,
                'rap_id' => $session->rap_id,
                'instructor2_id' => $session->instructor2_id,
                'course_id' => $session->course_id,
                'environment' => $session->course->environment->name,
                'headquarters' => optional(
                                optional($session->course->environment)->headquarters
                             )->name,
            ];
        });

        // Si no hay filtros, agrupar por mes después de paginar
        if (!$monthFilter && !$yearFilter) {
            $groupedSessions = $transformed->groupBy(function ($session) {
                return Carbon::parse($session['date'])->format('Y-m');
            });

            // Convertir a estructura paginada manteniendo la agrupación
            return response()->json([
                'data' => $groupedSessions,
                'current_page' => $paginatedSessions->currentPage(),
                'per_page' => $paginatedSessions->perPage(),
                'total' => $paginatedSessions->total(),
                'last_page' => $paginatedSessions->lastPage(),
            ]);
        }

        $paginatedSessions->setCollection($transformed);

        return response()->json([
            'data'         => $paginatedSessions->items(),
            'current_page' => $paginatedSessions->currentPage(),
            'per_page'     => $paginatedSessions->perPage(),
            'total'        => $paginatedSessions->total(),
            'last_page'    => $paginatedSessions->lastPage(),
        ]);
    }

    public function show($id)
    {
        $session = Session::included()->find($id);
        return response()->json($session);
    }



    public function sessionRap()
    {
        $sessions = Session::included()->filter()->get();
        return response()->json($sessions);
    }

    // Crear sesión
    public function createSession(Request $request)
    {
        return $this->sessionService->createSession($request);
    }
    // Actualizar sesión
    public function updateSessions(Request $request, ...$sessionIds)
    {
        return $this->sessionService->updateSessions($request, $sessionIds);
    }

    // Eliminar sesión po Id
    public function destroy($id)
    {
        return $this->sessionService->destroy($id);
    }

    // Eliminar sesiones por rango de fechas
    public function deleteSessionsByDateRange(Request $request)
    {
        return $this->sessionService->deleteSessionsByDateRange($request);
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
            ->orderBy('date', 'ASC')
            ->included()
            ->paginate(intval($elements), ['*'], 'page', $page);

        return response()->json($sessions);
    }

    public function filterOptions(Request $request)
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

        // Obtener el filtro de curso (si se envía)
        $courseFilter = $request->input('filter.course_');

        $sessionsQuery = Session::whereIn('course_id', $courseIds)
            ->with(['course', 'instructor.user', 'rap']);

        // Si se envía el filtro, limitar las sesiones al curso seleccionado
        if ($courseFilter) {
            $sessionsQuery->whereHas('course', function ($q) use ($courseFilter) {
                $q->where('code', $courseFilter);
            });
        }

        $sessions = $sessionsQuery->get();

        // Se obtienen los cursos únicos a partir de las sesiones
        $courses = $sessions->pluck('course')->unique('id')->values();

        $rapsByCourse = [];
        $instructorsByCourse = [];

        // Sólo se procesan rap e instructores si se ha enviado el filtro de curso
        if ($courseFilter) {
            foreach ($sessions as $session) {
                $courseCode = $session->course->code;
                $rap = $session->rap;
                $instructor = $session->instructor;

                if ($rap) {
                    if (!isset($rapsByCourse[$courseCode])) {
                        $rapsByCourse[$courseCode] = collect();
                    }
                    $rapsByCourse[$courseCode]->push($rap);
                }

                if ($instructor) {
                    if (!isset($instructorsByCourse[$courseCode])) {
                        $instructorsByCourse[$courseCode] = collect();
                    }
                    $instructorsByCourse[$courseCode]->push($instructor);
                }
            }

            foreach ($rapsByCourse as $courseCode => $raps) {
                $rapsByCourse[$courseCode] = $raps->unique('id')->values();
            }

            foreach ($instructorsByCourse as $courseCode => $instructors) {
                $instructorsByCourse[$courseCode] = $instructors->unique('id')->values();
            }
        }

        return response()->json([
            'courses' => $courses,
            // Si no se envía filtro de curso, se devuelven arrays vacíos para rap e instructores
            'rapsByCourse' => $courseFilter ? $rapsByCourse : [],
            'instructorsByCourse' => $courseFilter ? $instructorsByCourse : [],
        ]);
    }

    public function updateSessionsByRange(Request $request)
    {
        return $this->sessionService->updateSessionsByRange($request);
    }
}
