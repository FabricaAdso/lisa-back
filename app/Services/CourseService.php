<?php

namespace App\Services;

interface CourseService
{
    public function getInstructorAndSessions($request);
    public function getCourseInstructor($request);
    public function getCourseInstructorNow($request);
}
