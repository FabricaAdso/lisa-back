<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    //
    public function index()
    {
        $subjects = Subject::all();

        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|String|max:20',
            'total_number_hours' => 'required|integer',
            'program_id' => 'required|integer'
        ]);

        $subject = Subject::create($request->all());
        return response()->json($subject);
    }

    public function show($id)
    {
        $subject = Subject::find($id);
        return response()->json($subject);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|String|max:20',
            'total_number_hours' => 'required|integer',
            'program_id' => 'required|integer'
        ]);
        
        $subject = Subject::find($id);
        $subject->update($request->all());
        return response()->json($subject);
    }

    public function destroy($id)
    {
        $subject =  Subject::find($id);
        $subject->delete();
        return response()->json(['message' => 'Subject deleted successfully']);
    }
}
