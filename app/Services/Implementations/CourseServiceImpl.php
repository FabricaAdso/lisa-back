<?php

namespace App\Services\Implementations;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Session;
use App\Services\CourseService;
use Carbon\Carbon;

class CourseServiceImpl implements CourseService
{
    public function getInstructorAndSessions($request, $id)
    {
        $instructor = Instructor::findOrFail($id);
        $session = Session::where('instructor_id', $instructor->id)
        ->where(function ($query){
            $query->where('date', '>', Carbon::now()->toDateString())
                  ->orWhere(function ($query){
                    $query->where('date','=',Carbon::now()->toDateString())
                    ->where('start_time','>',Carbon::now()->toTimeString())
                    ->where('end_time','>',Carbon::now()->toTimeString());
                  });
        })->with('course')->get();
        return $session;
    }

    public function getCourseInstructor($request, $id)
    {
        $instructor = Instructor::findOrFail($id);
        $session = Session::where('instructor_id', $instructor->id)
        ->where(function ($query){
            $query->where('date', '<', Carbon::now()->toDateString())
                  ->orWhere(function ($query){
                    $query->where('date','=',Carbon::now()->toDateString())
                    ->where('start_time','<',Carbon::now()->toTimeString())
                    ->where('end_time','<',Carbon::now()->toTimeString());
                  });
        })->with('course')->get();
        return $session;
    }

    public function getCourseInstructorNow($request, $id)
    {
        $instructor = Instructor::findOrFail($id);
        $session = Session::where('instructor_id', $instructor->id)
        ->where(function ($query){
            $query->where('date', '=', Carbon::now()->toDateString())
                  ->orWhere(function ($query){
                    $query->where('date','=',Carbon::now()->toDateString())
                    ->where('start_time','<=',Carbon::now()->toTimeString())
                    ->where('end_time','>=',Carbon::now()->toTimeString());
                  });
        })->with('course')->get();
        return $session;
    }
        
}

