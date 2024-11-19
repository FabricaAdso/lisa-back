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
            'code' => 'sometimes|string|max:10',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
            'shift_id' => 'required|string',
            'state' => 'required|in:Lectiva,Productiva',
            'environment_id' => 'required|exists:environments,id',
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
            'code' => 'sometimes|string|max:10',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
            'shift_id' => 'required|string',
            'state' => 'required|in:Lectiva,Productiva',
            'environment_id' => 'required|exists:environments,id',
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
    
}
