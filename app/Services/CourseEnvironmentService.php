<?php
namespace App\Services;

use App\Models\Course;
use App\Models\Environment;

class CourseEnvironmentService
{
    public function assingCourseEnvitonment(array $environmentsIds, array $coursesIds)
    {
        $errores = [];
        foreach ($coursesIds as $courseId) {
            // Asegurarse de que $courseId es un objeto Course
            if (!$courseId instanceof Course) {
                $errores[] = "El elemento proporcionado no es un curso válido.";
                continue;
            }

            // Obtener los IDs de los ambientes asociados al curso
            $currentEnvironmentIds = $courseId->environments()->pluck('environments.id')->toArray(); // Ahora especificamos correctamente el pluck

            // Obtener las jornadas (shifts) asociadas al curso
            $currentShifts = $courseId->shifts()->get(); // Usamos get() para obtener todos los objetos Shift

            // Solo los nuevos ids de ambientes para evitar duplicados
            $newEnvironmentIds = array_diff($environmentsIds, $currentEnvironmentIds);

            foreach ($newEnvironmentIds as $environmentsId) {
                $environmet = Environment::findOrFail($environmentsId);

                // Verificar conflictos de horario en este ambiente
                foreach ($currentShifts as $shift) {
                    $conflic = $environmet->courses()
                        ->where(function ($query) use ($shift) {
                            $query->where('courses.start_time', '<', $shift->end_time)
                                ->where('courses.end_time', '>', $shift->start_time);
                        })
                        ->exists();

                    if ($conflic) {
                        $errores[] = "El curso {$courseId->name} no puede ser asignado al ambiente {$environmet->name} porque hay un conflicto de horario.";
                        continue 2; // Salir del ciclo de 'newEnvironmentIds' si hay un conflicto
                    }
                }

                // Si no hay conflictos, asignar el ambiente al curso
                $courseId->environments()->attach($environmet);
            }

            // Eliminar ambientes que ya no están en la lista de environments
            $environmentToRemove = array_diff($currentEnvironmentIds, $environmentsIds);
            if (!empty($environmentToRemove)) {
                $courseId->environments()->detach($environmentToRemove);
            }
        }

        // Retornar errores acumulados o mensaje de éxito
        return !empty($errores)
            ? ['error' => implode(', ', $errores)]
            : ['message' => 'Ambientes asignados correctamente sin conflictos.'];
    }

}