<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use Illuminate\Http\Request;

class ApprenticeController extends Controller
{
    //
    public function index()
    {
        $apprentices = Apprentice::all();
     //  $apprentices = Apprentice::included()->get();
        

        return response()->json($apprentices);
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'state' => 'required|in:formacion,Desertado,Etapa_productiva,Retiro_voluntario',

        ]);

        $apprentice = Apprentice::create($request->all());

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
