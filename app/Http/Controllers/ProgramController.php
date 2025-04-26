<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    public function index()
    {
        $user = User::with(['trainingCenters'])->find(Auth::id());
        $trainingId = $user->trainingCenters->pluck('id');

        $Program = Program::whereIn('training_center_id', $trainingId)->get();

        return response()->json($Program);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|String',
            'version' => 'required|String',
            'name' => 'required|String',
            'education_level_id' => 'required|exists:education_levels,id',
            'training_center_id' => 'required|exists:training_centers,id'
        ]);

        $Program = Program::create($request->all());
        return response()->json($Program);
    }

    public function show($id)
    {
        $Program = Program::find($id);
        return response()->json($Program);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|String',
            'version' => 'required|String',
            'name' => 'required|String',
            'education_level_id' => 'required|exists:education_levels,id',
            'training_center_id' => 'required|exists:training_centers,id'
        ]);

        $Program = Program::find($id);
        $Program->update($request->all());
        return response()->json($Program);
    }

    public function destroy($id)
    {
        $Program =  Program::find($id);
        $Program->delete();
        return response()->json(['message' => 'Programa deleted successfully']);
    }
}
