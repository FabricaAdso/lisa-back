<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        //$Programs = Program::all();
        $Programs = Program::included()->filter()->get();
        //$Programs = Program::included()->get();

        return response()->json($Programs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|String',
            'version' => 'required|String',
            'name' => 'required|String',
            'education_level_id' => 'required|exists:education_levels,id',
            'training_center_id' => 'required|exists:training_center_id,id'
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
            'training_center_id' => 'required|exists:training_center_id,id'
        ]);
        
        $Program = Program::find($id);
        $Program->update($request->all());
        return response()->json($Program);
    }

    public function destroy($id)
    {
        $Program =  Program::find($id);
        $Program->delete();
        return response()->json(['message' => 'Education level deleted successfully']);
    }
}
