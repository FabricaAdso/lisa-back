<?php

namespace App\Http\Controllers;

use App\Models\EnvironmentArea;
use Illuminate\Http\Request;

class EnvironmentAreaController extends Controller
{
    //
    public function index()
    {

        $environmentArea = EnvironmentArea::all();

        return response()->json($environmentArea);
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

        ]);

        $environmentArea = EnvironmentArea::create($request->all());

        return response()->json($environmentArea);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EnvironmentArea 
     * @return \Illuminate\Http\Response
     */
    public function show($id) //si se pasa $id se utiliza la comentada
    {
        $environmentArea = EnvironmentArea::included()->findOrFail($id);
        return response()->json($environmentArea);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EnvironmentArea
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100',
        ]);
        $environmentArea = EnvironmentArea::find($id);
        $environmentArea->update($request->all());

        return response()->json($environmentArea);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EnvironmentArea
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $environmentArea = EnvironmentArea::find($id);
        $environmentArea->delete();
        return response()->json($environmentArea);
    }
}
