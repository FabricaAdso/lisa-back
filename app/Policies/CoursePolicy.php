<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    public function updateRepresentative(User $user, Course $course, array $updateRepresentative): bool
    {
        $isLeader = $user->id == $course->course_leader_id;
        $modificarRepresentative = isset($updateRepresentative['representative_id']) || isset($updateRepresentative['co_representative_id']);
        return $isLeader && $modificarRepresentative;
    }

    public function updateLeader (User $user, Course $course, array $updateLeader): bool
    {
        $isCoodinator = $user->hasRole('Coordinador academico');
        $modificarLeader = isset($updateLeader['course_leader_id']);
        return $isCoodinator && $modificarLeader;
    }

}
