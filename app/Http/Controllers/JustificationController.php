<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\Justification;
use App\Models\Session;
use App\Models\User;
use App\Services\JustificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JustificationController extends Controller
{
    protected $justificationService;
    public function __construct(JustificationService $justificationService)
    {
        $this->justificationService = $justificationService;
    }
    public function checkAndUpdateExpiredJustifications()
    {
        return $this->justificationService->checkAndUpdateExpiredJustifications();
    }


    public function indexApprentice()
    {
        $user = User::find(Auth::id());        
        $apprentice = Apprentice::where('user_id', $user->id)->first();   
        if (!$apprentice) {
            return response()->json(['message' => 'Apprentice not found'], 404);
        }
        
        // Obtener todas las justificaciones asociadas al aprendiz.
        $justifications = Justification::whereIn('assistance_id', function ($query) use ($apprentice) {
            $query->select('id')
                ->from('assistances')
                ->where('apprentice_id', $apprentice->id);
        })->included()->filter()->get();
        return response()->json($justifications);
    }
    

    public function getInassitanceInstructor()
    {
        $user = User::find(Auth::id());
        $elements = request()->query('elements',15);
        $instructor = Instructor::where('user_id', $user->id)->first();
        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], 404);
        }
    
        $sessions = Session::where('instructor_id', $instructor->id)->get();
    
        // Obtener todas las justificaciones asociadas a las asistencias de esas sesiones, incluyendo la relación de usuario
        $justifications = Justification::whereIn('assistance_id', function($query) use ($sessions) {
            $query->select('id')
                ->from('assistances')
                ->whereIn('session_id', $sessions->pluck('id'));
        })->included()->filter()->paginate(intval($elements));

        return response()->json([$justifications]);
    }
    
    

    public function index()
    {
        $justifications = Justification::all();
        return response()->json($justifications);
    }


    public function show($id)
    {
        $justification = Justification::findOrFail($id)->included()->filter()->get();
        return response()->json($justification);
    }

    public function createJustification(Request $request)
    {
        $justification = $this->justificationService->createJustification($request);
        return response()->json($justification);
    }

}
