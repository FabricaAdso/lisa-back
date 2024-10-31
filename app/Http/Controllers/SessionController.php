<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    //
    public function index()
    {
        //$sessions = Session::all();
        $sessions = Session::included()->filter()->get();
        //$sessions = Session::included()->get();

        return response()->json($sessions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'user_id' => 'required|exists:users,id',
        ]);

        $session = Session::create($request->all());
        return response()->json($session);
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
            'education_level_id' => 'required|exists:education_levels,id'
        ]);

        $session =Session::find($id);
        $session->update($request->all());
        return response()->json($session);
    }

    public function destroy($id)
    {
        $session =  Session::find($id);
        $session->delete();
        return response()->json(['message' => 'Session deleted successfully']);
    }
}

