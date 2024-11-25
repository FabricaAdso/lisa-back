<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Http\Request;

class SessionController extends Controller
{

    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }
    

    public function index()
    {
        //$sessions = Session::all();
        $sessions = Session::included()->get();
        

        return response()->json($sessions);
    }


    public function show($id)
    {
        $session = Session::find($id);
        return response()->json($session);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:users,id',
            'instructor2_id' => 'nullable|exists:users,id',
        ]);

        $session = Session::find($id);
        $session->update($request->all());
        return response()->json($session);
    }


    public function destroy($id)
    {
        $session =  Session::find($id);
        $session->assistances()->delete();
        $session->delete();
        return response()->json(['message' => 'Session deleted successfully']);
    }

    // Crear sesión
    public function createSession(Request $request)
    {
        return $this->sessionService->createSession($request);
    }

}
