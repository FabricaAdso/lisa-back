<?php

namespace App\Services;

interface CourseService
{
    public function getInstructorAndSessions($request, $id);
    public function getCourseInstructor($request, $id);
    public function getCourseInstructorNow($request, $id);
}
