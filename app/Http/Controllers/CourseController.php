<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::included()->filter()->get();

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'code' => 'required|string|max:10',
            'program_id' => 'required|exists:programs,id',
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
        // Validar los datos
        $request->validate([
            'code' => 'required|string|max:10',
            'program_id' => 'required|exists:programs,id',
        ]);

        // Buscar el curso y actualizar con asignación masiva
        $course = Course::findOrFail($id);
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
}
