<?php
namespace App\Services;

use App\Models\Course;
use App\Models\Shift;

class CourseService
{

    public function checkShiftConflict($courseId, $shiftId)
    {
        // Obtener el curso
        $course = Course::find($courseId);
        // Obtener el turno
        $shift = Shift::find($shiftId);

        if (!$course || !$shift) {
            return false; // Si no existe el curso o el turno, no hay conflicto
        }

        // Verificar si el turno se solapa con alguno de los turnos asignados al curso
        $conflictingShifts = $course->shifts()->where(function ($query) use ($shift) {
            $query->where('start_time', '<', $shift->end_time)
                  ->where('end_time', '>', $shift->start_time);
        })->exists();

        return $conflictingShifts;
    }

    public function attachShiftToCourse($courseId, $shiftId)
    {
        $course = Course::find($courseId);
        $shift = Shift::find($shiftId);

        if ($course && $shift) {
            // Asociar el turno al curso
            $course->shifts()->attach($shift->id);
        }
    }

    public function validateAndAttachShift($data)
    {
        $courseId = $data['course_id'];
        $shiftId = $data['shift_id'];

        // Verificar si hay conflicto de turnos
        if ($this->checkShiftConflict($courseId, $shiftId)) {
            throw new \Exception("El turno ya está asignado en el intervalo de tiempo.");
        }

        // Si no hay conflicto, asociar el turno al curso
        $this->attachShiftToCourse($courseId, $shiftId);
    }
}