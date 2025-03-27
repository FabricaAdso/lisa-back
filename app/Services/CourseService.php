<?php

namespace App\Services;

interface CourseService
{
    public function getInstructorAndSessions($request);
    public function getCourseInstructor();
    public function getCourseInstructorNow($request);
}
