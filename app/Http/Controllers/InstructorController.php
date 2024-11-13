<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    //
    public function index()
    {
        //$instructor = Instructor::all();
       $instructor = Instructor::included()->get();


        return response()->json($instructor);
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'training_center_id'=>'required|exists:training_centers,id'

        ]);

        $instructor = Instructor::create($request->all());
        $instructor->courses()->attach($request->course_id,['start_date' => now()]);
        return response()->json($instructor);
    }

    public function show($id)
    {
        $instructor = Instructor::find($id);
        return response()->json($instructor);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'training_center_id'=>'required|exists:training_centers,id'
        ]);

        $instructor = Instructor::find($id);
        $instructor->update($request->all());
        return response()->json($instructor);
    }

    public function destroy($id)
    {
        $instructor =  Instructor::find($id);
        $instructor->delete();
        return response()->json(['message' => 'Instructor deleted successfully']);
    }
}
