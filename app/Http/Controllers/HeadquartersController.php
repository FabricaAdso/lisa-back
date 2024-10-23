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
      'name'=>'required|max:100',
      'adress'=>'required|max:100',
      'opening_time'=> 'required|date_format:h:i A',//formato de 12hr AM/PM,
      'closing_time'=> 'required|date_format:h:i A',//formato de 12hr AM/PM
      
        ]);
                // Convertir el formato de 12 horas a 24 horas
                $start_time_24 = \Carbon\Carbon::createFromFormat('h:i A', $request->opening_time)->format('H:i:s');
                $end_time_24 = \Carbon\Carbon::createFromFormat('h:i A', $request->closing_time)->format('H:i:s');

                $request->merge([
                    'opening_time' => $start_time_24,
                    'closing_time' => $end_time_24,
                ]);

        $headquarter = Headquarters::create($request->all());

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
    public function update(Request $request, Headquarters $headquarter)
    {
        $request->validate([
     'name'=>'required|max:100',
     'adress'=>'required|max:100',
     'opening_time'=>'required|max:100',
     'closing_time'=>'required|max:100',
        ]);

        $headquarter->update($request->all());

        return response()->json($headquarter);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Headquarters
     * @return \Illuminate\Http\Response
     */
    public function destroy(Headquarters $headquarter)
    {
        $headquarter->delete();
        return response()->json($headquarter);
    }
}
