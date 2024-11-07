<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Environment;

class EnvironmentService
{
    public function assignCoursesToEnvironment(array $courseIds, int $environmentId)
    {
        // Obtener el ambiente
        $environment = Environment::findOrFail($environmentId);

        // Obtener los cursos ya asignados al ambiente
        $environmentCourses = $environment->courses()->with('shifts')->get();

        foreach ($courseIds as $courseId) {
            $course = Course::findOrFail($courseId);
            $courseConflic = false;

            // Verificar si hay conflictos con los turnos de otros cursos asignados
            foreach ($environmentCourses as $existingCourse) {
                foreach ($existingCourse->shifts as $existingShift) {
                    $conflict = $course->shifts()
                        ->where('start_time', '<', $existingShift->end_time)
                        ->where('end_time', '>', $existingShift->start_time)
                        ->exists();

                    if ($conflict) {
                        return response()->json([
                            'message' => "Conflicto de horario: El curso $courseId tiene un conflicto con el curso {$existingCourse->id} en el ambiente $environmentId",
                        ], 409);
                    }
                }
            }

            // Si no hay conflicto, asignamos el curso al ambiente
            $environment->courses()->attach($courseId);
        }

        // Recargar los cursos del ambiente
        $environment->load('courses');

        return response()->json([
            'message' => 'Ambiente asignado correctamente',
            'ambiente' => $environment
        ]);
    }

    
}