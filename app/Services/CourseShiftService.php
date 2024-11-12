<?php
namespace App\Services;

use App\Models\Course;
use App\Models\Shift;

class CourseShiftService
{
    public function assignCourseShifts($shiftIds, $courseIds)
    {
        $errores = [];  // Para acumular los errores

        foreach ($courseIds as $courseId) {
            $course = Course::findOrFail($courseId);
            $currentShiftIds = $course->shifts()->pluck('shifts.id')->toArray();
            $arrayShift = is_array($shiftIds) ? $shiftIds : [];

            foreach ($shiftIds as $shiftId) {
                $shift = Shift::findOrFail($shiftId);
                // Verificar si ya está asignada la jornada
                if (in_array($shiftId, $currentShiftIds)) {
                    continue;  // Si la jornada ya está asignada, no hacer nada
                }
                $conflic = $course->shifts()
                    ->where(function ($query) use ($shift) {
                        $query->where('shifts.start_time', '<', $shift->end_time)
                            ->where('shifts.end_time', '>', $shift->start_time);
                    })->exists();

                if ($conflic) {
                    $errores[] = "La jornada '{$shift->name}' se cruza con una ya asignada al curso '{$course->code}'.";
                }
            }

            // Eliminar las jornadas que ya no están en la lista
            $shiftToRemove = array_diff($currentShiftIds, $arrayShift);
            if (!empty($shiftToRemove)) {
                $course->shifts()->detach($shiftToRemove);
            }
            // Usar sync para sincronizar las jornadas
            $course->shifts()->sync(array_unique($arrayShift));  // Sincroniza las jornadas, evitando duplicados

        }

        if (!empty($errores)) {
            return ['error' => implode(', ', $errores)]; // Retornar los errores acumulados
        }

        return ['message' => 'Jornadas actualizadas correctamente.']; // Si todo va bien
    }
}