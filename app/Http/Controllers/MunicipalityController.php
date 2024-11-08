<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    //
    public function index($departament_id)
    {

        $municipalities = Municipality::where('departament_id', $departament_id)->get();


        if ($municipalities->isEmpty()) {
            return response()->json(['message' => 'No municipalities found for this department.'], 404);
        }


        return response()->json($municipalities);
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

        $municipalities = Municipality::create($request->all());

        return response()->json($municipalities);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Municipality
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $municipalities = Municipality::included()->findOrFail($id);
        return response()->json($municipalities);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Municipality
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Municipality $municipalities)
    {
        $request->validate([
            'name' => 'required|max:100',

        ]);

        $municipalities->update($request->all());

        return response()->json($municipalities);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Municipality
     * @return \Illuminate\Http\Response
     */
    public function destroy(Municipality $municipalities)
    {
        $municipalities->delete();
        return response()->json($municipalities);
    }
}
