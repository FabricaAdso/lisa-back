<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use Illuminate\Http\Request;

class EnvironmentController extends Controller
{
    //
    public function index()
    {
      
       // $environments = Environment::all();
        $environments = Environment::included()->get();
    
        return response()->json($environments);
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
      'capacity'=>'required|max:100',
        ]);

        $environments = Environment::create($request->all());

        return response()->json($environments);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Environment 
     * @return \Illuminate\Http\Response
     */
    public function show($id) //si se pasa $id se utiliza la comentada
    {  
        $environments = Environment::included()->findOrFail($id);
        return response()->json($environments);


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Environment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Environment $environments)
    {
        $request->validate([
     'name'=>'required|max:100',
     'capacity'=>'required|max:100',
        ]);

        $environments->update($request->all());

        return response()->json($environments);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Environment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Environment $environments)
    {
        $environments->delete();
        return response()->json($environments);
    }
}
