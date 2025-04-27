<?php

namespace App\Services\Implementations;

use App\Models\Apprentice;
use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Justification;
use App\Models\Session;
use App\Models\User;
use App\Services\CourseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseServiceImpl implements CourseService
{
  public function getInstructorAndSessions($request)
  {
    $user = User::find(Auth::id());
    $instructor = Instructor::where('user_id', $user->id)->first();
    if (!$instructor) {
      return ['message' => 'instructor no encontrado'];
    }
    $session = Session::where('instructor_id', $instructor->id)
      ->where(function ($query) {
        $query->where('date', '>', Carbon::now()->toDateString())
          ->orWhere(function ($query) {
            $query->where('date', '=', Carbon::now()->toDateString())
              ->where('start_time', '>', Carbon::now()->toTimeString())
              ->where('end_time', '>', Carbon::now()->toTimeString());
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
    if (!$instructor) {
      return ['message' => 'instructor no encontrado'];
    }
    $session = Session::where('instructor_id', $instructor->id)
      ->where(function ($query) {
        $query->where('date', '<', Carbon::now()->toDateString())
          ->orWhere(function ($query) {
            $query->where('date', '<', Carbon::now()->toDateString())
              ->where('start_time', '<', Carbon::now()->toTimeString())
              ->where('end_time', '<', Carbon::now()->toTimeString());
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

  public function search($request)
  {
    // Obtener IDs de los centros de formación del usuario
    $user        = User::with('trainingCenters')->find(Auth::id());
    $trainingIds = $user->trainingCenters->pluck('id')->all();

    // Parámetro de búsqueda
    $code = $request->query('code', '');

    // Construcción de la consulta
    $courses = Course::whereHas('program.trainingCenter', function ($q) use ($trainingIds) {
      $q->whereIn('training_centers.id', $trainingIds);
    })
      // filtro por código si se proporcionó
      ->when($code, function ($q) use ($code) {
        $q->where('code', 'like', "%{$code}%");
      })
      // eager load de relaciones en lugar de included()
      ->with(['program', 'program.trainingCenter'])
      // scopes adicionales (si usas alguno, opcional)
      ->filter()
      ->get();

    return $courses;
  }

  public function deleteAllRelations($id)
  {
    $user = User::find(Auth::id())->load('trainingCenters');
    // 2. Recuperar curso con su centro a través de program.trainingCenter
    $course = Course::with('program.trainingCenter')
      ->findOrFail($id);

    // 3. Validar que el usuario pertenece al mismo centro
    $courseCenterId = $course->program->trainingCenter->id;
    if (! $user->trainingCenters->pluck('id')->contains($courseCenterId)) {
      return response()->json([
        'error'   => 'forbidden',
        'message' => 'No autorizado: centro de formación distinto'
      ], 403);
    }
    DB::transaction(function () use ($id) {
      $courseId = $id;

      // 1. Eliminar aprobaciones relacionadas
      Aprobation::whereHas('justification.assistance.session', function ($query) use ($courseId) {
        $query->where('course_id', $courseId);
      })->delete();

      // 2. Eliminar justificaciones relacionadas
      Justification::whereHas('assistance.session', function ($query) use ($courseId) {
        $query->where('course_id', $courseId);
      })->delete();

      // 3. Eliminar asistencias relacionadas
      Assistance::whereHas('session', function ($query) use ($courseId) {
        $query->where('course_id', $courseId);
      })->delete();

      // 4. Eliminar sesiones del curso
      Session::where('course_id', $courseId)->delete();

      // 5. Eliminar aprendices del curso
      Apprentice::where('course_id', $courseId)->delete();

      // 6. Eliminar el curso
      Course::destroy($courseId);
    });

    return response()->json([
      'succes'   => 'eliminado',
    ], 200);
  }
}
