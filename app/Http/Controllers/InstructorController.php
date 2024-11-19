<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
      // Método index: Listar instructores
      public function index(Request $request)
      {
          // Obtener el ID del centro actual (ficticio por ahora)
          $trainingCenterId = $request->input('training_center_id', 1); 
          
          $instructors = Instructor::byTrainingCenter($trainingCenterId)->get();
  
          return response()->json($instructors);
      }
}
