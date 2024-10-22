<?php

namespace App\Http\Controllers;

use App\Models\Departament;
use Illuminate\Http\Request;

class DepartamentController extends Controller
{
    //
    public function index()
    {
      
      //  $departaments = Departament::all();
        $departaments = Departament::included()->get();
    
        return response()->json($departaments);
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
      'municipality_id'=>'required|max:100',
      
        ]);

        $departaments = Departament::create($request->all());

        return response()->json($departaments);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\departaments 
     * @return \Illuminate\Http\Response
     */
    public function show($id) //si se pasa $id se utiliza la comentada
    {  
        $departaments = Departament::included()->findOrFail($id);
        return response()->json($departaments);


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Departament
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Departament $departaments)
    {
        $request->validate([
     'name'=>'required|max:100',
     
        ]);

        $departaments->update($request->all());

        return response()->json($departaments);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Departament
     * @return \Illuminate\Http\Response
     */
    public function destroy(Departament $departaments)
    {
        $departaments->delete();
        return response()->json($departaments);
    }
}
