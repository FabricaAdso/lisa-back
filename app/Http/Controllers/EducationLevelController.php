<?php

namespace App\Http\Controllers;

use App\Models\EducationLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class EducationLevelController extends Controller
{
    public function index()
    {
        $educationLevels = EducationLevel::all();

        return response()->json($educationLevels);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|String|max:20'
        ]);

        $educationLevel = EducationLevel::create($request->all());
        return response()->json($educationLevel);
    }

    public function show($id)
    {
        $educationLevel = EducationLevel::find($id);
        return response()->json($educationLevel);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|String|max:20'
        ]);
        
        $educationLevel = EducationLevel::find($id);
        $educationLevel->update($request->all());
        return response()->json($educationLevel);
    }

    public function destroy($id)
    {
        $educationLevel =  EducationLevel::find($id);
        $educationLevel->delete();
        return response()->json(['message' => 'Education level deleted successfully']);
    }
}
