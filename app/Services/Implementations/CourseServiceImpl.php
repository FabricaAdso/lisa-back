<?php

namespace App\Services\Implementations;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Session;
use App\Models\User;
use App\Services\CourseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CourseServiceImpl implements CourseService
{
    public function getInstructorAndSessions($request)
    {
      $user = User::find(Auth::id());
        $instructor = Instructor::where('user_id', $user->id)->first();
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

    public function getCourseInstructor($request)
    {
      $user = User::find(Auth::id());
      $instructor = Instructor::where('user_id', $user->id)->first();
        $session = Session::where('instructor_id', $instructor)
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

    public function getCourseInstructorNow($request)
    {
      $user = User::find(Auth::id());
      $instructor = Instructor::where('user_id', $user->id)->first();
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

