<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Services\TokenService;
use Illuminate\Http\Request;

class InstructorController extends Controller
{

    protected  $token_service;

    function __construct(TokenService $token_service)
    {   
        $this->token_service = $token_service;
    }
    //
    public function index()
    {
       // $instructor = Instructor::all();
      $instructor = Instructor::included()->get();


        return response()->json($instructor);
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'state' => 'required|in:Activo,Inactivo',
        ]);
        $training_center_id = $this->token_service->getTrainingCenterIdFromToken();
        //echo($training_center_id);
        $instructor = Instructor::create([
            'user_id' => $request->user_id,
            'training_center_id'=>$training_center_id
        ]);
   
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
            'training_center_id'=>'required|exists:training_centers,id',
            'state' => 'required|in:Activo,Inactivo',
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
