<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HeadquartersController extends Controller
{
    //
    public function index()
    {

        //$headquarter = Headquarters::all();
        // $headquarter = Headquarters::included()->get();
        $headquarter = Headquarters::included()->filter()->get();

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
        // Obtener el usuario autenticado
        $user = User::find(Auth::id());

        // Verificar si el usuario tiene centros de formación asociados
        $trainingCenterId = $user->trainingCenters->first()->id;

        $request->validate([
            'name' => 'required|max:100',
            'adress' => 'required|max:100',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'municipality' => 'required|max:100',
        ]);

        // Verificar si ya existe una sede con los mismos parámetros
        $existingHeadquarter = Headquarters::where('name', $request->name)
            ->where('adress', $request->adress)
            ->where('municipality', $request->municipality)
            ->where('training_center_id', $trainingCenterId)
            ->first();

        if ($existingHeadquarter) {
            return response()->json(['message' => 'Ya existe una sede con los mismos datos en este centro de formación'], 409);
        }

        // Establecer el training_center_id del usuario autenticado
        $headquarter = Headquarters::create([
            'name' => $request->name,
            'adress' => $request->adress,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'municipality' => $request->municipality,
            'training_center_id' => $trainingCenterId,  // Establecer el centro de formación del usuario autenticado
        ]);

        $headquarter->load('trainingCenter');
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
        // Obtener el usuario autenticado
        $user = User::find(Auth::id());

        // Obtener el training_center_id del usuario autenticado
        $trainingCenterId = $user->trainingCenters->first()->id;

        $request->validate([
            'name' => 'required|max:100',
            'adress' => 'required|max:100',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after: opening_time',
            'municipality' => 'required|max:100',
        ]);

        // Buscar la sede para actualizar
        $headquarter = Headquarters::findOrFail($id);

        // Actualizar el registro
        $headquarter->update([
            'name' => $request->name,
            'adress' => $request->adress,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'municipality' => $request->municipality,
            'training_center_id' => $trainingCenterId,  // Confirmar que se mantenga el centro de formación del usuario
        ]);

        $headquarter->load('trainingCenter');
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
