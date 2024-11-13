<?php
namespace App\Services;

use App\Models\Course;
use App\Models\Shift;

class CourseShiftService
{
    public function assignCourseShifts(array $shiftIds, array $courses)
    {
        $errores = [];  // Para acumular errores

        foreach ($courses as $course) {
            // Asegurarse de que cada elemento en $courses es un objeto Course
            if (!$course instanceof Course) {
                return ['error' => "El curso no es válido."];
            }
            

            // Obtener los IDs de jornadas actualmente asignadas al curso
            $currentShiftIds = $course->shifts()->pluck('shifts.id')->toArray();

            // 1. Filtrar los IDs de jornadas nuevas que aún no están asignadas al curso
            $newShiftIds = array_diff($shiftIds, $currentShiftIds);

            // 2. Revisión de conflictos de horario para cada nueva jornada
            foreach ($newShiftIds as $shiftId) {
                $shift = Shift::findOrFail($shiftId);

                $conflicto = $course->shifts()
                    ->where(function ($query) use ($shift) {
                        $query->where('shifts.start_time', '<', $shift->end_time)
                              ->where('shifts.end_time', '>', $shift->start_time);
                    })
                    ->exists();

                if ($conflicto) {
                    $errores[] = "La jornada '{$shift->name}' se cruza con otra ya asignada al curso '{$course->code}'.";
                    continue;
                }

                // 3. Si no hay conflicto, agregar la jornada al curso
                $course->shifts()->attach($shiftId);
            }

            // 4. Eliminar jornadas que ya no están en la lista de shiftIds
            $shiftsToRemove = array_diff($currentShiftIds, $shiftIds);
            if (!empty($shiftsToRemove)) {
                $course->shifts()->detach($shiftsToRemove);
            }
        }

        // Retornar errores acumulados o mensaje de éxito
        return !empty($errores)
            ? ['error' => implode(', ', $errores)]
            : ['message' => 'Jornadas asignadas correctamente sin conflictos.'];
    }
}