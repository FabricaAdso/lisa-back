<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Services\TokenService;
use Illuminate\Http\Request;

class ApprenticeController extends Controller
{
    protected  $token_service;

    function __construct(TokenService $token_service)
    {   
        $this->token_service = $token_service;
    }

    public function index()
    {
     //  $apprentices = Apprentice::all();
         $apprentices = Apprentice::byTrainingCenter()->included()->filter()->get();
        

        return response()->json($apprentices);
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'state' => 'required|in:formacion,Desertado,Etapa_productiva,Retiro_voluntario',

        ]);
        $training_center_id = $this->token_service->getTrainingCenterIdFromToken();
        $apprentice = Apprentice::create([
            'course_id' => $request->course_id,
            'state' => $request->state,
            'user_id' => $request->user_id,
            'training_center_id'=>$training_center_id
        ]);
        
        return response()->json($apprentice);
    }

    public function show($id)
    {
        $apprentice = Apprentice::find($id);
        return response()->json($apprentice);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'state' => 'required|in:formacion,Desertado,Etapa_productiva,Retiro_voluntario',
        ]);
        

        $apprentice = Apprentice::find($id);
        $apprentice->update($request->all());
        return response()->json($apprentice);
    }

    public function destroy($id)
    {
        $apprentice =  Apprentice::find($id);
        $apprentice->delete();
        return response()->json(['message' => 'Apprentice deleted successfully']);
    }
}
