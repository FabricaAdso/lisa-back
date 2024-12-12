<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Shift;
use App\Services\CourseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    protected $courseService;
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index()
    {
        $courses = Course::included()->filter()->get();
        //$courses = Course::included()->get();

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'code' => 'required|string|max:10',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
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

       // Eliminar un curso
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }
    
    public function getInstructorAndSessions(Request $request, $id)
    {
        $courseInstructorSession = $this->courseService->getInstructorAndSessions($request, $id);
        return response()->json([
            'las fichas que tienen sesion con el instructor son' => $courseInstructorSession,
        ]);
    }

    public function getCourseInstructor(Request $request, $id)
    {
        $courseIntructor = $this->courseService->getCourseInstructor($request, $id);
        return response()->json([
            'las fichas donde el instructor tuvo formacion son:' => $courseIntructor,
        ]);
    }

    public function getCourseInstructorNow(Request $request, $id)
    {
        $courseIntructor = $this->courseService->getCourseInstructorNow($request, $id);
        return response()->json([
            'las fichas donde el instructor tiene sesiones actualmente:' => $courseIntructor,
        ]);
    }
}
