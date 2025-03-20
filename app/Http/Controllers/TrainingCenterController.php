<?php

namespace App\Http\Controllers;

use App\Models\TrainingCenter;
use Illuminate\Http\Request;

class TrainingCenterController extends Controller
{
    //
    public function index()
    {

        // $trainingCenter = TrainingCenter::all();
        $trainingCenter = TrainingCenter::included()->filter()->get();

        return response()->json($trainingCenter);
    }
    public function trainingCenter()
    {

        // $trainingCenter = TrainingCenter::all();
        $elements = request()->query('elements', 10);
        $trainingCenter = TrainingCenter::included()->filter()->paginate(intval($elements));

        return response()->json($trainingCenter);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|max:100',
            'code' => 'required|max:100',
            'regional_id' => 'required|exists:regionals,id',
        ]);

        $trainingCenter = TrainingCenter::create($request->all());

        return response()->json($trainingCenter);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tipo_Transaccion  
     * @return \Illuminate\Http\Response
     */
    public function show($id) //si se pasa $id se utiliza la comentada
    {
        $trainingCenter = TrainingCenter::included()->findOrFail($id);
        return response()->json($trainingCenter);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TrainingCenter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validar los datos de entrada
        $request->validate([
            'name' => 'required|max:100',
            'code' => 'required|max:100',
            'regional_id' => 'required|exists:regionals,id',
        ]);

        $trainingCenter = TrainingCenter::find($id);
        if (!$trainingCenter) {
            return response()->json(['error' => 'Centro de formación no encontrado'], 404);
        }

        $existsTrainingCenterCode = TrainingCenter::where('code', $request->code)
            ->where('id', '!=', $id)
            ->exists();
        if ($existsTrainingCenterCode) {
            return response()->json(['error' => 'El código ya está en uso por otro centro de formación'], 409);
        }

        $trainingCenter->update($request->all());

        return response()->json($trainingCenter, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrainingCenter
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrainingCenter $trainingCenter)
    {
        $trainingCenter->delete();
        return response()->json($trainingCenter);
    }
}
