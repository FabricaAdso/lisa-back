<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Environment;
use Illuminate\Support\Facades\DB;

class EnvironmentService
{
    public function assignCoursesToEnvironment($courseIds, $environmentId)
    {
        // Obtener el ambiente
        $environment = Environment::findOrFail($environmentId);

        foreach ($courseIds as $courseId) {
            $course = Course::findOrFail($courseId);

            // Obtener los intervalos de fechas del curso a asignar
            $courseStart = $course->date_start;
            $courseEnd = $course->date_end;

            // Comprobar conflictos de horario en la tabla pivote
            $conflict = DB::table('course_environment')
                ->join('courses', 'course_environment.course_id', '=', 'courses.id')
                ->where('course_environment.environment_id', $environmentId)
                ->where(function ($query) use ($courseStart, $courseEnd) {
                    $query->whereBetween('courses.date_start', [$courseStart, $courseEnd])
                          ->orWhereBetween('courses.date_end', [$courseStart, $courseEnd])
                          ->orWhere(function ($query) use ($courseStart, $courseEnd) {
                              $query->where('courses.date_start', '<', $courseStart)
                                    ->where('courses.date_end', '>', $courseEnd);
                          });
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'error' => "El curso con ID {$courseId} tiene un conflicto de horario en el ambiente '{$environment->name}'"
                ], 409);
            }

            // Si no hay conflicto, asignar el curso al ambiente
            $environment->courses()->attach($courseId);
        }

        return response()->json(['message' => 'Cursos asignados exitosamente al ambiente.']);
    }   
}