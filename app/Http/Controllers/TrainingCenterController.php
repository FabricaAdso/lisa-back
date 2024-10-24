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
        $trainingCenter = TrainingCenter::included()->get();

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
            'municipality_id' => 'required|max:100',

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
    public function update(Request $request, TrainingCenter $trainingCenter)
    {
        $request->validate([
            'name' => 'required|max:100',
        ]);

        $trainingCenter->update($request->all());

        return response()->json($trainingCenter);
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
