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
      if(!$instructor){
        return ['message' => 'instructor no encontrado'];
      }
        $session = Session::where('instructor_id', $instructor->id) 
          ->where(function ($query){
            $query->where('date', '>', Carbon::now()->toDateString())
            ->orWhere(function ($query){
              $query->where('date','=',Carbon::now()->toDateString())
              ->where('start_time','>',Carbon::now()->toTimeString())
              ->where('end_time','>',Carbon::now()->toTimeString());
            });
          })
          ->included()
          ->orderBy('date')
          ->orderBy('start_time')
          ->get();
        return $session;
    }

    public function getCourseInstructor()
    {
      $user = User::find(Auth::id());
      $instructor = Instructor::where('user_id', $user->id)->first();
      if(!$instructor){
        return ['message' => 'instructor no encontrado'];
      }
        $session = Session::where('instructor_id', $instructor->id)
        ->where(function ($query){
            $query->where('date', '<', Carbon::now()->toDateString())
                  ->orWhere(function ($query){
                    $query->where('date','=',Carbon::now()->toDateString())
                    ->where('start_time','<',Carbon::now()->toTimeString())
                    ->where('end_time','<',Carbon::now()->toTimeString());
                  });
        })
        ->included()
        ->orderBy('date')
        ->orderBy('start_time')
        ->get();
        return $session;
    }

    //sesiones del dia
    public function getCourseInstructorNow($request)
    {
      $user = User::find(Auth::id());
      $instructor = Instructor::where('user_id', $user->id)->first();
      
      $fichas = Course::whereHas('sessions', function ($q) use ($instructor) {
        $q->where('instructor_id', $instructor->id);
      })->get();

      $sesionesCercanas = [];
      foreach ($fichas as $ficha) {
        $sesionesCercana = Session::where('instructor_id', $instructor->id)
          ->where('course_id', $ficha->id)
          ->where('date', '>=', Carbon::now()->toDateString())
          ->orderBy('created_at', 'asc')
          ->included()
          ->first();

        if ($sesionesCercana) {
          $sesionesCercanas[] = $sesionesCercana;
        }
      }
      return $sesionesCercanas;
      
      // if(!$instructor){
      //   return ['message' => 'instructor no encontrado'];
      // }
      //   $session = Session::where('instructor_id', $instructor->id)
      //   ->where(function ($query){
      //       $query->where('date', '=', Carbon::now()->toDateString())
      //             ->orWhere(function ($query){
      //               $query->where('date','=',Carbon::now()->toDateString())
      //               ->where('start_time','<=',Carbon::now()->toTimeString())
      //               ->where('end_time','>=',Carbon::now()->toTimeString());
      //             });
      //   })
      //   ->included()
      //   ->orderBy('date')
      //   ->orderBy('start_time')
      //   ->first();
      //   return $session;
    }
        
}

