<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Shift;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CourseController extends Controller
{
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
            'program_id' => 'required|exists:programs,id',
        ]);

        // Asignación masiva
        $course = Course::create($request->all());
        
        return response()->json($course, 201);
    }

    public function show($id)
    {
        $course = Course::findOrFail($id)->included()->filter()->get();
        return response()->json($course);
    }

    public function update(Request $request, $id)
    {
        // Validar los datos
        $request->validate([
            'code' => 'required|string|max:10',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
            'program_id' => 'required|exists:programs,id',
        ]);

        // Buscar el curso y actualizar con asignación masiva
        $course = Course::findOrFail($id);
        $course->update($request->all());
        $course->load(['program','shifts']);
        return response()->json($course);
    }

       // Eliminar un curso
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    //asignar curso a una jornada
    public function updateShifts(Request $request, $courseId)
    {
        // Validar la entrada
        $request->validate([
            'shift_ids' => 'required|array',
            'shift_ids.*' => 'integer|exists:shifts,id'
        ]);
    
        // Obtener el curso o lanzar un error si no existe
        try {
            $course = Course::findOrFail($courseId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'curso no encontrado.'], 404);
        }
    
        // Obtener las IDs de las jornadas actuales asignadas al curso
        $currentShiftIds = $course->shifts()->pluck('shifts.id')->toArray();
    
        // Verificar si hay cruces de jornadas
        foreach ($request->shift_ids as $shiftId) {
            try {
                $shift = Shift::findOrFail($shiftId);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'jornada no encontrada.'], 404);
            }
    
            // Validar si los días asignados a la jornada nueva se cruzan con las jornadas existentes del curso
            $conflictingShifts = $course->shifts()
                ->whereHas('days', function($query) use ($shift) {
                    $query->whereIn('days.id', $shift->days->pluck('id')->toArray());
                })
                ->where(function ($query) use ($shift) {
                    $query->where('shifts.start_time', '<', $shift->end_time)
                          ->where('shifts.end_time', '>', $shift->start_time);
                })->get();
    
            if ($conflictingShifts->isNotEmpty()) {
                return response()->json([
                    'error' => "La jornada '{$shift->name}' se cruza con una ya asignada al curso '{$course->code}'"
                ], 400);
            }
    
            // Validar que la jornada no esté ya asignada a otro curso
            if ($shift->courses()->where('courses.id', '!=', $courseId)->exists()) {
                return response()->json([
                    'error' => "La jornada '{$shift->name}' ya está asignada a otro curso."
                ], 400);
            }
        }
    
        // Eliminar las jornadas que ya no están en la lista
        $shiftsToRemove = array_diff($currentShiftIds, $request->shift_ids);
        if (!empty($shiftsToRemove)) {
            $course->shifts()->detach($shiftsToRemove);
        }
    
        // Agregar las nuevas jornadas
        $shiftsToAdd = array_diff($request->shift_ids, $currentShiftIds);
        if (!empty($shiftsToAdd)) {
            $course->shifts()->attach($shiftsToAdd);
        }
        
        $course->load('shifts');
        return response()->json([
            'message' => 'Jornadas actualizadas correctamente.',
            'course' => $course
        ]);
    }
    
    
}
