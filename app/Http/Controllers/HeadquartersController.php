<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use Illuminate\Http\Request;

class HeadquartersController extends Controller
{
    //
    public function index()
    {

        //   $headquarter = Headquarters::all();
        $headquarter = Headquarters::included()->get();
        $headquarter = Headquarters::included()->filter()->get();
        $headquarter->load('municipality.departament', 'trainingCenter');
        return response()->json($headquarter);
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
            'adress' => 'required|max:100',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after: opening_time ',

        ]);
        // Convertir el formato de 12 horas a 24 horas
        $start_time_24 = \Carbon\Carbon::createFromFormat('H:i', $request->opening_time)->format('H:i:s');
        $end_time_24 = \Carbon\Carbon::createFromFormat('H:i', $request->closing_time)->format('H:i:s');

        $request->merge([
            'opening_time' => $start_time_24,
            'closing_time' => $end_time_24,
        ]);

        $headquarter = Headquarters::create($request->all());
        $headquarter->load('municipality.departament', 'trainingCenter');
        return response()->json($headquarter);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Headquarters 
     * @return \Illuminate\Http\Response
     */
    public function show($id) //si se pasa $id se utiliza la comentada
    {
        $headquarter = Headquarters::included()->findOrFail($id);
        return response()->json($headquarter);
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
        $request->validate([
            'name' => 'required|max:100',
            'adress' => 'required|max:100',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after: opening_time',

        ]);
        $start_time_24 = \Carbon\Carbon::createFromFormat('H:i', $request->opening_time)->format('H:i:s');
        $end_time_24 = \Carbon\Carbon::createFromFormat('H:i', $request->closing_time)->format('H:i:s');

        $request->merge([
            'opening_time' => $start_time_24,
            'closing_time' => $end_time_24,
        ]);

        $headquarter = Headquarters::find($id);

        $headquarter->update($request->all());
        $headquarter->load('municipality.departament', 'trainingCenter');
        return response()->json($headquarter);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Headquarters
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $headquarter = Headquarters::find($id);
        $headquarter->delete();
        return response()->json($headquarter);
    }
}
