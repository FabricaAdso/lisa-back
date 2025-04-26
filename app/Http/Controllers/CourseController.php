<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Session;
use App\Models\User;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use function PHPSTORM_META\map;

class CourseController extends Controller
{
    protected $courseService;
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index()
    {
        $user = User::with(['trainingCenters'])->find(Auth::id());
        $trainingId = $user->trainingCenters->pluck('id');
        $paginate = request()->query('elements', 10);

        $courses = Course::whereHas('program.trainingCenter', function ($query) use ($trainingId) {
            $query->where('training_centers.id',$trainingId);
        })
            ->included()
            ->filter()
            ->paginate(intval($paginate));

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'code' => 'required|string|max:10',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
            'end_date_training_stage' => 'required|date|after:date_start',
            'shift' => 'required|String',
            'state' => 'required|in:Terminada_por_fecha,En_ejecucion,Terminada,Termindad_por_unificacion',
            'stage' => 'required|in:PRACTICA,LECTIVA',
            'program_id' => 'required|exists:programs,id',
            'course_leader_id' => 'nullable|exists:instructors,id',
            'representative_id' => 'nullable|exists:apprentices,id',
            'co_representative_id' => 'nullable|exists:apprentices,id',
        ]);

        // Asignación masiva
        $course = Course::create($request->all());

        return response()->json($course, 201);
    }

    public function show($id)
    {
        $course = Course::with('program')->findOrFail($id);
        return response()->json($course);
    }

    public function courseByCourseLeader() {
        $user = User::find(Auth::id());
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            // Si no se encuentra un instructor, devolver un mensaje de error
            return response()->json(['error' => 'Instructor no encontrado'], 404);
        }
        $courses = Course::where('course_leader_id', $instructor->id)
                            ->where('state','En_ejecucion')
                            ->get();
        return response()->json($courses);
    }

    public function update(Request $request, $id)
    {
        // Buscar el curso 
        $course = Course::findOrFail($id);

        //usar la policity para vocero y co-vocero
        Gate::authorize('updateRepresentative', [$course, $request->only(['representative_id', 'co_representative_id'])]);
        //usar la policity para lider de ficha
        Gate::authorize('updateLeader', [$course, $request->only(['course_leader_id'])]);
        // Validar los datos
        $request->validate([
            'code' => 'required|string|max:10',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
            'end_date_training_stage' => 'required|date|after:date_start',
            'shift' => 'required|String',
            'state' => 'required|in:Terminada_por_fecha,En_ejecucion,Terminada,Termindad_por_unificacion',
            'stage' => 'required|in: PRACTICA, LECTIVA',
            'program_id' => 'required|exists:programs,id',
            'course_leader_id' => 'nullable|exists:instructors,id',
            'representative_id' => 'nullable|exists:apprentices,id',
            'co_representative_id' => 'nullable|exists:apprentices,id',
        ]);

        //actualizar con asignación masiva
        $course->update($request->all());

        return response()->json($course);
    }

    public function asignarLider($idCourse, $idLeader) {
        $course = Course::find($idCourse);
        $leader = Instructor::find($idLeader);

        if (!$leader || !$course) {
            return response()->json(['error' => 'elemento no encontrado'], 404);
        }

        $course->course_leader_id = $idLeader;
        $course->save();

        return response()->json($course);
    } 

    // Eliminar un curso
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    public function getInstructorAndSessions(Request $request)
    {
        $courseInstructorSession = $this->courseService->getInstructorAndSessions($request);
        return response()->json(
            $courseInstructorSession
        );
    }

    public function getCourseInstructor()
    {
        $courseIntructor = $this->courseService->getCourseInstructor();
        return response()->json($courseIntructor);
    }

    public function getCourseInstructorNow(Request $request)
    {
        $courseIntructor = $this->courseService->getCourseInstructorNow($request);
        return response()->json($courseIntructor);
    }

    public function search(Request $request){
        $response = $this->courseService->search($request);
        return response()->json($response);
    }

    public function deleteAllRelations($id) {
        $response = $this->courseService->deleteAllRelations($id);
        return $response;
    }

}
